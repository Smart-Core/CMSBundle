<?php 

namespace SmartCore\Bundle\EngineBundle\Service;

use SmartCore\Bundle\EngineBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
//use Symfony\Component\DependencyInjection\ContainerAware;
use SmartCore\Bundle\EngineBundle\Entity\Folder as FolderEntity;
use SmartCore\Bundle\EngineBundle\Entity\FolderRepository;

class Folder extends Controller // ContainerAware
{
    protected $container;
    protected $db;
    protected $env;

    /**
     * @var FolderRepository
     */
    protected $folderRepo;

    /**
     * Constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->db  = $container->get('engine.db');
        $this->env = $container->get('engine.env');
        $this->folderRepo = $this->getRepo('SmartCoreEngineBundle:Folder');
    }

    /**
     * Получить данные о папке по её ID.
     *
     * @param int $folder_id
     * @param string $language - указать извлекаемый язык (пока не испольузется.)
     *
     * @return FolderEntity
     */
    public function getDataById($folder_id, $language = false)
    {
        return $this->folderRepo->findOneBy(array(
            'is_active' => true,
            'is_deleted' => false,
            'folder_id' => $folder_id,
        ));
    }

    /**
     * Получить данные о папке.
     *
     * @param string $uri_part - запрашиваемый чать УРИ
     * @param int $pid - искать в родительском ID.
     * @param string $language - указать извлекаемый язык (пока не испольузется.)
     * @return object|false
     */
    public function getData($uri_part, $parent_folder, $language = false)
    {
        return $this->folderRepo->findOneBy(array(
            'is_active' => true,
            'is_deleted' => false,
            'uri_part' => empty($uri_part) ? null : $uri_part,
            'parent_folder' => $parent_folder
        ));
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

        while($folder_id != 1) {
            $folder = $this->getDataById($folder_id);
            if ($folder) {
                //$folder_id = $folder->pid;
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
        $folder_pid = null;
        $router_node_id = null;
        $path_parts = explode('/', $slug);

        /** @var $folder FolderEntity */
        foreach ($path_parts as $key => $segment) {
            // Проверка строки запроса на допустимые символы.
            // @todo сделать проверку на разрешение круглых скобок.
            if (!empty($segment) and !preg_match('/^[a-z_@0-9.-]*$/iu', $segment)) {
                $data['status'] = 404;
                break;
            }

            // заканчиваем работу, если имя папки пустое и папка не является корневой 
            // т.е. обрабатываем последнюю запись в строке УРИ
            if('' == $segment and 0 != $key) { 
                // @todo здесь надо делать обработчик "файла" т.е. папки с выставленным флагом "is_file".
                break;
            }

            // В данной папке есть нода которой передаётся дальнейший парсинг URI.
            if ($router_node_id !== null) {
                // выполняется часть URI парсером модуля и возвращается результат работы, в дальнейшем он будет передан самой ноде.
                $ModuleRouter = $this->forward($router_node_id . '::router', array(
                    'slug' => str_replace($current_folder_path, '', substr($this->container->get('request')->getBaseUrl() . '/', 0, -1) . $slug))
                );

                // Роутер модуля вернул положительный ответ.
                if ($ModuleRouter->isOk()) {
                    $data['node_route'] = array(
                        'id' => $router_node_id,
                        'response' => $ModuleRouter,
                    );
                    // В случае успешного завершения роутера модуля, роутинг ядром прекращается.
                    break; 
                }
                
                unset($ModuleRouter);
            }

            if ($folder = $this->getData($segment, $folder_pid)) {
                if ( true ) { // @todo if ($this->Permissions->isAllowed('folder', 'read', $folder->permissions)) {
                    // Заполнение мета-тегов.
                    foreach ($folder->getMeta() as $meta_name => $meta_value) {
                        $data['meta'][$meta_name] = $meta_value;
                    }

                    if ($folder->getUriPart()) {
                        $current_folder_path .= $folder->getUriPart() . '/';
                    }

                    if ($folder->getTemplate()) {
                        $data['template'] = $folder->getTemplate();
                    }
                    
                    $folder_pid = $folder; //$folder_pid = $folder->folder_id;
                    $router_node_id = $folder->getRouterNodeId(); //$router_node_id = $folder->router_node_id;
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
