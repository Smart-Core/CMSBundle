<?php 

namespace SmartCore\Bundle\EngineBundle\Engine;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Folder
{
    protected $container;
    protected $env;

    /**
     * @var \SmartCore\Bundle\EngineBundle\Entity\FolderRepository
     */
    protected $folderRepository;

    /**
     * Constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->env = $container->get('engine.env');
        $this->folderRepository = $container->get('doctrine.orm.entity_manager')->getRepository('SmartCoreEngineBundle:Folder');
    }

    /**
     * Получение полной ссылки на папку, указав её id. Если не указать ид папки, то вернётся текущий путь.
     * 
     * @param int $folder_id
     * @return string $uri
     */
    public function getUri($folder_id = false)
    {
        if ($folder_id === false) {
            $folder_id = $this->env->get('current_folder_id');
        }

        $uri = '/';
        $uri_parts = array();

        /** @var $folder \SmartCore\Bundle\EngineBundle\Entity\Folder */
        while($folder_id != 1) {
            $folder = $this->folderRepository->findOneBy(array(
                'is_active' => true,
                'is_deleted' => false,
                'folder_id' => $folder_id,
            ));
            if ($folder) {
                $folder_id = $folder->getParentFolder()->getId();
                $uri_parts[] = $folder->getUriPart();
            } else{
                break;
            }
        }

        $uri_parts = array_reverse($uri_parts);
        foreach ($uri_parts as $value) {
            $uri .= $value . '/';
        }
    
        return $this->container->get('request')->getBaseUrl() . $uri;
    }
    
    /**
     * Роутинг.
     * 
     * @param string $slug
     * @return array
     */
    public function router($slug)
    {
        $data = array(
            'folders' => array(),
            'meta' => array(),
            'status' => 200,
            'template' => 'index',
            'node_route' => null, // @todo
        );
        
        // @todo при обращении к фронт-контроллеру /web/app.php не коррекнтно определяется активные пункты меню.
        $current_folder_path = $this->container->get('request')->getBaseUrl() . '/';
        $parent_folder = null;
        $router_node_id = null;
        $path_parts = explode('/', $slug);

        /** @var $folder \SmartCore\Bundle\EngineBundle\Entity\Folder */
        foreach ($path_parts as $key => $segment) {
            // Проверка строки запроса на допустимые символы.
            // @todo сделать проверку на разрешение круглых скобок.
            if (!empty($segment) and !preg_match('/^[a-z_@0-9.-]*$/iu', $segment)) {
                $data['status'] = 404;
                break;
            }

            // Закончить работу, если имя папки пустое и папка не является корневой т.е. обрабатывается последняя запись в строке УРИ
            if('' == $segment and 0 != $key) { 
                // @todo здесь надо делать обработчик "файла" т.е. папки с выставленным флагом "is_file".
                break;
            }

            // В данной папке есть нода которой передаётся дальнейший парсинг URI.
            if ($router_node_id !== null) {
                // Выполняется часть URI парсером модуля и возвращается результат работы, в дальнейшем он будет передан самой ноде.
                // @todo запрос ноды только для получения имени модуля не сосвсем красиво...
                // может быть как-то кешировать это дело, либо хранить имя модуля прямо в таблице папок, например в виде массива router_node_id и router_node_module.
                $node = $this->container->get('engine.node_manager')->get($router_node_id);

                /** @var $ModuleRouter \SmartCore\Bundle\EngineBundle\Module\RouterResponse */
                $ModuleRouter = $this->container->get('kernel')->getBundle($node->getModule() . 'Module')
                    ->router($node, str_replace($current_folder_path, '', substr($this->container->get('request')->getBaseUrl() . '/', 0, -1) . $slug));

                // Роутер модуля вернул положительный ответ. Статус 200.
                if ($ModuleRouter->isOk()) {
                    $data['node_route'] = array(
                        'id' => $router_node_id,
                        'response' => $ModuleRouter,
                    );
                    // В случае успешного завершения роутера модуля, роутинг ядром прекращается.
                    break; 
                } else {
                    // @todo сделать 404 и 403
                }
                
                unset($ModuleRouter);
            }

            $folder = $this->folderRepository->findOneBy(array(
                'is_active' => true,
                'is_deleted' => false,
                'uri_part' => empty($segment) ? null : $segment,
                'parent_folder' => $parent_folder
            ));

            if ($folder) {
                if ( true ) { // @todo if ($this->Permissions->isAllowed('folder', 'read', $folder->permissions)) {
                    foreach ($folder->getMeta() as $meta_name => $meta_value) {
                        $data['meta'][$meta_name] = $meta_value;
                    }

                    if ($folder->getUriPart()) {
                        $current_folder_path .= $folder->getUriPart() . '/';
                    }

                    if ($folder->getTemplate()) {
                        $data['template'] = $folder->getTemplate();
                    }
                    
                    $parent_folder = $folder;
                    $router_node_id = $folder->getRouterNodeId();
                    $folder->setUri($current_folder_path);
                    $data['folders'][$folder->getId()] = $folder;
                    $this->env->set('current_folder_id', $folder->getId());
                    $this->env->set('current_folder_path', $current_folder_path);
                } else {
                    $data['status'] = 403;
                }
            } else {
                $data['status'] = 404;
            }
        }

        return $data;
    }
}
