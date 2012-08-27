<?php 

namespace SmartCore\Bundle\EngineBundle\Engine;

use SmartCore\Bundle\EngineBundle\Controller\Controller;

class JavaScriptLibrary extends Controller
{
    /**
     * Список всех прописаных скриптов.
     * @var array
     */
    protected $scripts;
    
    /**
     * Профиль по умолчанию.
     * @var string
     */
    protected $default_profile;
    
    /**
     * Список профилей, которые можно применять.
     * @var string
     */
    protected $profiles;
    
    /**
     * Список запрошенных библиотек.
     */
    protected $requested_libs = array();
    
    protected $table_libs;
    protected $table_paths;
    
    /**
     * Constructor.
     */
    public function __construct($DB)
    {
//        parent::__construct();
        $this->DB = $DB;
        
        // @todo пока принимается только один профиль, далее надо сделать перебор...
        $this->default_profile  = 'local';
        //$this->profiles = $this->Settings->getParam('scripts_profiles');
        $this->profiles         = 'local';
        $this->scripts          = array();
        $this->table_libs       = $this->DB->prefix() . 'javascript_library';
        $this->table_paths      = $this->DB->prefix() . 'javascript_library_paths';
        
        $sql = "SELECT script_id, name, related_by, current_version, default_profile, files FROM {$this->table_libs} ORDER BY pos DESC ";
        $result = $this->DB->query($sql);
        while($row = $result->fetchObject()) {
            $this->scripts[$row->name] = array(
                'script_id' => $row->script_id,
                'related_by' => $row->related_by,
                'current_version' => $row->current_version,
                'default_profile' => $row->default_profile,
                'files' => $row->files,
                // не обязательные свойства.
                //'title' => $row->title,
                //'homepage' => $row->homepage,
                //'descr' => $row->descr,
                );
        }
    }
    
    /**
     * Подключение библиотеки скриптов.
     *
     * @param string $name
     * @param string $version
     */
    public function request($name, $version = false)
    {
        $this->requested_libs[$name] = $version;
    }

    /**
     * Получить список запрошенных либ.
     *
     * @return array
     */
    public function all()
    {
        $output = array();
        
        // В связи с тем, что запрашивается в произвольном порядке - сначала надо сформировать массив с ключами в правильном порядке.
        foreach ($this->scripts as $key => $value) {
            $output[$key] = false;
        }

        // Затем вычисляются зависимости.
        $flag = 1;
        while ($flag == 1) {
            $flag = 0;
            foreach ($this->requested_libs as $name => $value) {
                // @todo пока можно обработать зависимость только от одной либы, далее надо сделать списки, например "prototype, scriptaculous".
                if (!empty($this->scripts[$name]['related_by']) and !isset($this->requested_libs[$this->scripts[$name]['related_by']])) {
                    $this->requested_libs[$this->scripts[$name]['related_by']] = false;
                    $flag = 1;
                }
            }
        }

        // @todo сделать возможность конфигурирования из файлов.
        foreach ($this->requested_libs as $name => $version) {
            $sql_version = empty($version) ? " AND version = '{$this->scripts[$name]['current_version']}' " : " AND version = '$version' ";
            
            $sql = "SELECT path
                FROM {$this->table_paths}
                WHERE script_id = '" . $this->scripts[$name]['script_id'] . "'
                AND profile = '{$this->profiles}'
                $sql_version ";                
            $result = $this->DB->query($sql);
            if ($result->rowCount() == 1) {
                $row = $result->fetchObject();
                $path = strpos($row->path, 'http://') === 0 ? $row->path : $this->engine('env')->global_assets . $row->path; // @todo https://  и просто //
            } else {
                $sql = "SELECT path 
                    FROM {$this->table_paths}
                    WHERE script_id = '" . $this->scripts[$name]['script_id'] . "'
                    AND profile = '{$this->default_profile}'
                    $sql_version ";
                $path = $this->engine('env')->global_assets . $this->DB->fetchObject($sql)->path;
            }
            
            foreach (explode(',', $this->scripts[$name]['files']) as $file) {
                if (substr($file, strrpos($file, '.') + 1) === 'css') {
                    $output[$name]['css'][] = $path . $file;
                }

                if (substr($file, strrpos($file, '.') + 1) === 'js') {
                    $output[$name]['js'][] = $path . $file;
                }
            }
        }
        
        // Удаляются пустые ключи
        foreach ($output as $key => $value) {
            if ($output[$key] === false) {
                unset($output[$key]);
            }
        }
        
        return $output;
    }
    
    /**
     * Получить весь список доступных скриптов.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->scripts;
    }
}