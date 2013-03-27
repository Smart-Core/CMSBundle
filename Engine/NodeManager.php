<?php

namespace SmartCore\Bundle\EngineBundle\Engine;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Form\FormTypeInterface;
use SmartCore\Bundle\EngineBundle\Entity\Node;

class NodeManager extends ContainerAware
{
    protected $db;

    /**
     * Список всех нод, запрошенных в текущем контексте.
     * @var array
     */
    protected $nodes_list = array();

    /**
     * Создание ноды
     *
     * @param Node $node
     */
    public function createNode(Node $node)
    {
        $module = $this->container->get('kernel')->getBundle($node->getModule() . 'Module');

        if (method_exists($module, 'createNode')) {
            $module->createNode($node);
        }
    }

    /**
     * Получить форму редактирования параметров подключения модуля.
     *
     * @param string $module_name
     * @return FormTypeInterface
     */
    public function getPropertiesFormType($module_name)
    {
        $reflector = new \ReflectionClass(get_class($this->container->get('kernel')->getBundle($module_name . 'Module')));
        $form_class_name = '\\' . $reflector->getNamespaceName() . '\Form\Type\NodePropertiesFormType';

        if (class_exists($form_class_name)) {
            return new $form_class_name;
        } else {
            // @todo может быть гибче настраивать форму параметров по умолчанию?.
            return new \SmartCore\Bundle\EngineBundle\Form\Type\NodeDefaultPropertiesFormType();
        }
    }

    /**
     * Получить объект ноды.
     *
     * @param int $node_id
     * @return Node
     */
    public function get($node_id)
    {
        if (isset($this->nodes_list[$node_id])) {
            return $this->nodes_list[$node_id];
        }

        return $this->container->get('doctrine')->getManager()->find('SmartCoreEngineBundle:Node', $node_id);

        /*
        // @todo потестить...
        if ($node = $this->container->get('engine.cache')->getNode($node_id)) {
            return $node;
        } else {
            return $this->container->get('doctrine')->getManager()->find('SmartCoreEngineBundle:Node', $node_id);
        }
        */
    }

    /**
     * Создание списка всех запрошеных нод, в каких блоках они находятся и с какими 
     * параметрами запускаются модули.
     * 
     * @param array     $parsed_uri
     * @return array    $nodes_list
     */
    public function buildNodesList(array $router_data)
    {
        $folders = $router_data['folders'];

        if (!empty($this->nodes_list)) {
            return $this->nodes_list;
        }

        $this->db = $this->container->get('engine.db');

        $used_nodes = array();
        $lockout_nodes = array(
            'single'  => array(), // Блокировка нод в папке, без наследования.
            'inherit' => array(), // Блокировка нод в папке, с наследованием.
            'except'  => array(), // Блокировка всех нод в папке, кроме заданных.
        );

        /** @var $folder \SmartCore\Bundle\EngineBundle\Entity\Folder */
        foreach ($folders as $folder) {
            // single каждый раз сбрасывается и устанавливается заново для каждоый папки.
            // @todo блокировку нод.
            /*
            $lockout_nodes['single'] = array();
            if (isset($parsed_uri_value['lockout_nodes']['single']) and !empty($parsed_uri_value['lockout_nodes']['single'])) {
                //$lockout_nodes['single'] = $parsed_uri_value['lockout_nodes']['single'];
                $tmp = explode(',', $parsed_uri_value['lockout_nodes']['single']);
                foreach ($tmp as $single_value) {
                    $t = trim($single_value);
                    if (!empty($t)) {
                        $lockout_nodes['single'][trim($single_value)] = 'blocked'; // ставлю тупо 'blocked', но главное в массиве с блокировками, это индексы.
                    }
                }
            }

            // Блокировка нод в папке, с наследованием.
            if (isset($parsed_uri_value['lockout_nodes']['inherit']) and !empty($parsed_uri_value['lockout_nodes']['inherit'])) {
                $tmp = explode(',', $parsed_uri_value['lockout_nodes']['inherit']);
                foreach ($tmp as $inherit_value) {
                    $t = trim($inherit_value);
                    if (!empty($t)) {
                        $lockout_nodes['inherit'][trim($inherit_value)] = 'blocked'; // ставлю тупо 'blocked', но главное в массиве с блокировками, это индексы.
                    }
                }
            }

            // Блокировка всех нод в папке, кроме заданных.
            if (isset($parsed_uri_value['lockout_nodes']['except']) and !empty($parsed_uri_value['lockout_nodes']['except'])) {
                $tmp = explode(',', $parsed_uri_value['lockout_nodes']['except']);
                foreach ($tmp as $except_value) {
                    $t = trim($except_value);
                    if (!empty($t)) {
                        $lockout_nodes['except'][trim($except_value)] = 'blocked'; // ставлю тупо 'blocked', но главное в массиве с блокировками, это индексы.
                    }
                }
            }
            */

            // @todo сейчас список нод запрашивается плоским SQL, надо как-то на ORM перевести.
            $sql = false;

            // @todo запаковать database_table_prefix в конфиг движка.
            $database_table_prefix = $this->container->getParameter('database_table_prefix');

            // Обработка последней папки т.е. текущей.
            if ($folder->getId() == $this->container->get('engine.env')->get('current_folder_id')) {
                $sql = "SELECT *
                    FROM {$database_table_prefix}engine_nodes
                    WHERE folder_id = '{$folder->getId()}'
                    AND is_active = '1'
                ";
                // исключаем ранее включенные ноды.
                foreach ($used_nodes as $used_nodes_value) {
                    $sql .= " AND node_id != '{$used_nodes_value}'";
                }
                $sql .= ' ORDER BY position';
            } else if ($folder->getHasInheritNodes()) { // в этой папке есть ноды, которые наследуются...
                $sql = "SELECT n.*
                    FROM {$database_table_prefix}engine_nodes AS n,
                        {$database_table_prefix}engine_blocks_inherit AS bi
                    WHERE n.block_id = bi.block_id 
                        AND is_active = 1
                        AND n.folder_id = '{$folder->getId()}'
                        AND bi.folder_id = '{$folder->getId()}'
                    ORDER BY n.position ASC
                ";
            }

            // В папке нет нод для сборки.
            if ($sql === false) {
                continue;
            }

            $result = $this->db->query($sql);
            while ($row = $result->fetchObject()) {
                /*
                if ($this->Permissions->isAllowed('node', 'read', $row->permissions) == 0) {
                    continue;
                }*/

                // Создаётся список нод, которые уже в включены.
                if ($folder->getHasInheritNodes()) {
                    $used_nodes[] = $row->node_id; 
                }

                $this->nodes_list[$row->node_id] = '_dummy';
            }
        }

        foreach ($lockout_nodes['single'] as $node_id => $value) {
            unset($this->nodes_list[$node_id]);
        }

        foreach ($lockout_nodes['inherit'] as $node_id => $value) {
            unset($this->nodes_list[$node_id]);
        }

        if (!empty($lockout_nodes['except'])) {
            foreach ($this->nodes_list as $node_id => $value) {
                if (!array_key_exists($node_id, $lockout_nodes['except'])) {
                    unset($this->nodes_list[$node_id]);
                }
            }
        }

        $is_cached = true;
        $cache = $this->container->get('engine.cache');
        $nodes = array();
        $list = '';
        foreach ($this->nodes_list as $node_id => $_dummy) {
            $list .= $node_id . ',';

            if ($cache->hasNode($node_id)) {
                $nodes[] = $cache->getNode($node_id);
            } else {
                $is_cached = false;
            }
        }

        if (strlen($list)) {
            if (!$is_cached) {
                $em = $this->container->get('doctrine')->getManager();
                $list = substr($list, 0, strlen($list)-1);
                $query = $em->createQuery("
                    SELECT n
                    FROM SmartCoreEngineBundle:Node n
                    WHERE n.node_id IN({$list})
                    ORDER BY n.position ASC
                ");
                $nodes = $query->getResult();
            }

            // Приведение массива в вид с индексами в качестве ID нод.
            /** @var $node Node */
            foreach ($nodes as $node) {
                if (!$is_cached) {
                    $cache->setNode($node);
                }

                if (isset($router_data['node_route']['response']) and $router_data['node_route']['id'] == $node->getId()) {
                    $node->setRouterResponse($router_data['node_route']['response']);
                }

                $this->nodes_list[$node->getId()] = $node;
            }
        }

        return $this->nodes_list;
    }
}
