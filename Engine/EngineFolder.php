<?php 

namespace SmartCore\Bundle\EngineBundle\Engine;

use Symfony\Component\DependencyInjection\ContainerInterface;
use SmartCore\Bundle\EngineBundle\Entity\Folder;
use SmartCore\Bundle\EngineBundle\Form\Type\FolderFormType;

class EngineFolder
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \SmartCore\Bundle\EngineBundle\Entity\FolderRepository
     */
    protected $repository;

    /**
     * Constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->repository = $this->em->getRepository('SmartCoreEngineBundle:Folder');
    }

    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param mixed $data    The initial data for the form
     * @param array $options Options for the form
     *
     * @return \Symfony\Component\Form\Form
     */
    public function createForm($data = null, array $options = [])
    {
        return $this->container->get('form.factory')->create(new FolderFormType(), $data, $options);
    }

    /**
     * Create folder.
     *
     * @return Folder
     */
    public function create()
    {
        return new Folder();
    }

    /**
     * Поиск по родительской папке.
     *
     * @param Folder $parent_folder
     * @return array
     */
    public function findByParent(Folder $parent_folder = null)
    {
        return $this->repository->findBy(['parent_folder' => $parent_folder]);
    }

    /**
     * Get folder.
     *
     * @return Folder
     */
    public function get($id)
    {
        return $this->repository->find($id);
    }

    /**
     * Обновление папки.
     */
    public function update(Folder $folder)
    {
        $this->em->persist($folder);
        $this->em->flush();
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
            $folder_id = $this->container->get('engine.context')->getCurrentFolderId();
        }

        $uri = '/';
        $uri_parts = [];

        /** @var $folder \SmartCore\Bundle\EngineBundle\Entity\Folder */
        while ($folder_id != 1) {
            $folder = $this->repository->findOneBy([
                'is_active'  => true,
                'is_deleted' => false,
                'folder_id'  => $folder_id,
            ]);

            if ($folder) {
                $folder_id = $folder->getParentFolder()->getId();
                $uri_parts[] = $folder->getUriPart();
            } else {
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
        $data = [
            'folders' => [],
            'meta' => [],
            'status' => 200,
            'template' => 'index',
            'node_route' => null, // @todo
        ];
        
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
                    $data['node_route'] = [
                        'id' => $router_node_id,
                        'response' => $ModuleRouter,
                    ];
                    // В случае успешного завершения роутера модуля, роутинг ядром прекращается.
                    break; 
                } else {
                    // @todo сделать 404 и 403
                }
                
                unset($ModuleRouter);
            }

            $folder = $this->repository->findOneBy([
                'is_active' => true,
                'is_deleted' => false,
                'uri_part' => empty($segment) ? null : $segment,
                'parent_folder' => $parent_folder
            ]);

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

                    $this->container->get('engine.context')->setCurrentFolderId($folder->getId());
                    $this->container->get('engine.context')->setCurrentFolderPath($current_folder_path);
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
