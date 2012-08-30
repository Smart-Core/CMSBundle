<?php 

namespace SmartCore\Bundle\EngineBundle\Service;

use SmartCore\Bundle\EngineBundle\Controller\Controller;

class Folder extends Controller
{
    protected $_sql_is_active = ' AND is_active = 1 ';
    
    protected $_folder_tree_list_arr = array();
    protected $_folder_tree = array();
    protected $_tree_link = array();
    protected $_tree_level = 0;

    /**
     * Получить данные о папке по её ID.
     *
     * @param int $folder_id
     * @param string $language - указать извлекаемый язык (пока не испольузется.)
     * @return object
     */
    public function getDataById($folder_id, $language = false)
    {
        $sql = "SELECT *
            FROM {$this->DB->prefix()}engine_folders
            WHERE site_id = '{$this->engine('site')->getId()}'
            {$this->_sql_is_active}
            AND is_deleted = 0
            AND folder_id = '{$folder_id}' ";

        return $this->DB->fetchObject($sql);
    }

    /**
     * Получить данные о папке.
     *
     * @param string $uri_part - запрашиваемый чать УРИ
     * @param int $pid - искать в родительском ID.
     * @param string $language - указать извлекаемый язык (пока не испольузется.)
     * @return object|false
     */
    public function getData($uri_part, $pid, $language = false)
    {
        $sql = "SELECT *
            FROM {$this->DB->prefix()}engine_folders
            WHERE site_id = '{$this->engine('site')->getId()}'
            {$this->_sql_is_active}
            AND is_deleted = 0
            AND uri_part = '{$uri_part}'
            AND pid = '{$pid}' ";

        return $this->DB->fetchObject($sql);
    }

    /**
     * Получить плоский список папок. Уровень вложенности указывается значением 'level'.
     *
     * @param
     * @return array
     */
    public function getList()
    {
        $this->buildTree(0, 0);
        return $this->getTreeList();
    }

    /**
     * Получение "плоского списка" папок вида:
     * 
     * [1] => Array
     *   (
     *     [title] => Главная
     *     [link] => /
     *     [level] => 0
     *   )
     *
     * @return array
     */
    public function getTreeList()
    {
        if (count($this->_folder_tree_list_arr) == 0) {
            $this->_getTreeList($this->_folder_tree);
        }
        
        return $this->_folder_tree_list_arr;
    }
    
    /**
     * Вспомогательный метод.
     * 
     * @param array $data
     */
    private function _getTreeList($data)
    {
        foreach ($data as $key => $value) {
            $this->_folder_tree_list_arr[$value['folder_id']] = array(
                'title'        => $value['title'],
                'link'        => $value['link'],
                'is_active'    => $value['is_active'],
                'pos'        => $value['pos'],
                'level'        => $this->_tree_level,
                );
            
            if (count($value['folders']) > 0) {
                $this->_tree_level++;
                $this->_getTreeList($value['folders']);
            }
        }
        
        $this->_tree_level--;
    }
        
    /**
     * Построение дерева папок.
     * 
     * @param int $parent_id
     * @param int $max_depth - максимальная вложенность
     */
    public function buildTree($parent_id, $max_depth = false, &$tree = false)
    { 
        $sql = "SELECT *
            FROM {$this->DB->prefix()}engine_folders
            WHERE site_id = '{$this->engine('site')->getId()}'
            {$this->_sql_is_active}
            AND is_deleted = 0
            AND pid = '{$parent_id}'
            ORDER BY pos ";
        $result = $this->DB->query($sql);
        if ($result->rowCount() > 0) {
            $this->_tree_level++;
            
            while ($row = $result->fetchObject()) {
                if ($parent_id > 0) {
                    $this->_tree_link[$this->_tree_level] = $row->uri_part;
                }
                
                $uri = $this->engine('env')->get('base_url');
                foreach ($this->_tree_link as $value) {
                    $uri .= $value . '/';
                }
                
                if ($max_depth != false and $max_depth < $this->_tree_level) { // копаем до указанной глубины.
                    continue;
                }

                $tree[$row->folder_id] = array(
                    'folder_id' => $row->folder_id,
                    'is_active' => $row->is_active,
                    'pid'       => $row->pid,
                    'pos'       => $row->pos,
                    'link'      => $uri,
                    'title'     => $row->title,
                    'folders'   => array(),
                    );

                if ($parent_id == 0) {
                    $this->_folder_tree = &$tree;
                }

                $this->buildTree($row->folder_id, $max_depth, $tree[$row->folder_id]['folders']);
            }
            unset($this->_tree_link[$this->_tree_level]);
            $this->_tree_level--;
        }
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
            $folder_id = $this->engine('env')->get('current_folder_id');
        }

        $uri_parts = array();
        $uri = '';
        
        while($folder_id != 1) {
            $folder = $this->getDataById($folder_id);
            if ($folder !== false) {
                $folder_id = $folder->pid;
                $uri_parts[] = $folder->uri_part;
            } else{
                break;
            }
        }

        $uri_parts = array_reverse($uri_parts);
        foreach ($uri_parts as $value) {
            $uri .= $value . '/';
        }
    
        return $this->engine('env')->get('base_url') . $uri;
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
        );
        
        // @todo при обращении к фронт-контроллеру /web/app.php не коррекнтно определяется активные пункты меню.
        $current_folder_path = $this->engine('env')->get('base_path');
        $router_node_id = null;
        $folder_pid = 0;

        $path_parts = explode('/', $slug);
        
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
                // @todo видимо здесь надо делать обработчик "файла" т.е. папки с выставленным флагом "is_file".
                break;
            }

            // В данной папке есть нода которой передаётся дальнейший парсинг URI.
            if ($router_node_id !== null) {
                // выполняется часть URI парсером модуля и возвращается результат работы, в дальнейшем он будет передан самой ноде.
                $ModuleRouter = $this->forward($router_node_id . '::router', array(
                    'slug' => str_replace($current_folder_path, '', substr($this->engine('env')->base_path, 0, -1) . $slug))
                );
                
                // Роутер модуля вернул положительный ответ.
                if ($ModuleRouter->isOk()) {
                    $data['folders'][$folder->folder_id]['router_response'] = $ModuleRouter;
                    $data['folders'][$folder->folder_id]['router_node_id'] = $router_node_id;
                    // В случае успешного завершения роутера модуля, роутинг ядром прекращается.
                    break; 
                }
                
                unset($ModuleRouter);
            } // __end if ($router_node_id !== null)

            $folder = $this->getData($segment, $folder_pid);
            
            if ($folder !== false) {
                //if ($this->Permissions->isAllowed('folder', 'read', $folder->permissions)) {
                if ( true ) {
                    // Заполнение мета-тегов.
                    if (!empty($folder->meta)) {
                        foreach (unserialize($folder->meta) as $key2 => $value2) {
                            $data['meta'][$key2] = $value2;
                        }
                    }

                    if ($folder->uri_part !== '') {
                        $current_folder_path .= $folder->uri_part . '/';
                    }

                    // Чтение макета для папки.
                    // @todo возможно ненадо. оставить только один view.
                    if (!empty($folder->layout)) {
                        $data['template'] = $folder->layout;
                    }
                    
                    $folder_pid = $folder->folder_id;
                    $router_node_id = $folder->router_node_id;
                    $data['folders'][$folder->folder_id] = array(
                        'uri' => $current_folder_path,
                        'title' => $folder->title,
                        'descr' => $folder->descr,
                        'is_inherit_nodes' => $folder->is_inherit_nodes,
                        'lockout_nodes' => unserialize($folder->lockout_nodes),
                    );
                    $this->engine('env')->set('current_folder_id', $folder->folder_id);
                    $this->engine('env')->set('current_folder_path', $current_folder_path);
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
