<?php 

namespace SmartCore\Bundle\EngineBundle\Engine;

use SmartCore\Bundle\EngineBundle\Controller\Controller;

class Theme extends Controller
{
    protected $paths;
    protected $template;
    protected $theme_path;

    protected $css_path;
    protected $js_path;
    protected $img_path;

    protected $ini;
    protected $vendor_path;

    /**
     * Constructor.
     */
    public function __construct($path = '/')
    {
//        parent::__construct();
        $this->theme_path   = $path;
        $this->css_path     = $path . 'css/';
        $this->js_path      = $path . 'js/';
        $this->img_path     = $path . 'img/';
        $this->ini          = array();
    }

    public function setPaths($paths)
    {
        $this->paths = $paths;
    }

    public function setTemplate($name)
    {
        $this->template = $name;
    }

    public function setThemePath($path)
    {
        $this->theme_path = $path;
    }

    protected function parseIni($template)
    {
        foreach ($this->paths as $path) {
            $ini_file = $path . '/' . $template . '.ini';
            if (file_exists($ini_file)) {
                $current_ini = parse_ini_file($ini_file, true);

                if (isset($current_ini['extend'])) {
                    $this->parseIni($current_ini['extend']);
                }

                $this->ini = $current_ini + $this->ini;
            }
        }
    }

    public function processConfig($View)
    {
        $this->paths        = $View->getPaths();
        $this->template     = $View->getTemplateName();
        $this->vendor_path  = $View->assets['vendor'];
        $this->theme_path   = $View->assets['theme_path'];
        $this->img_path     = $View->assets['theme_img_path'];
        $this->css_path     = $View->assets['theme_css_path'];
        $this->js_path      = $View->assets['theme_js_path'];

        krsort($this->paths);

        $this->parseIni($this->template);

//        sc_dump($this->ini);

        if (isset($this->ini['img_path'])) {
            $this->img_path = $this->theme_path . $this->ini['img_path'];
        }

        if (isset($this->ini['css_path'])) {
            $this->css_path = $this->theme_path . $this->ini['css_path'];
        }

        if (isset($this->ini['js_path'])) {
            $this->js_path = $this->theme_path . $this->ini['js_path'];
        }

        foreach ($this->ini as $key => $value) {
            switch ($key) {
                case 'doctype':
                    $this->Html->doctype($value);
                    break;
                case 'css':
                    $css_list = explode(',', $value);
                    foreach ($css_list as $css_filename) {
                        $css = trim($css_filename);
                        if ( ! empty($css) ) {
                            if (false !== strpos($css, '{VENDOR}')) {
                                $css = str_replace('{VENDOR}', $this->vendor_path, $css);
                            } else {
                                $css = $this->css_path . $css;
                            }

                            $this->Html->css($css);
                        }
                    }
                    break;
                case 'js':
                    $js_list = explode(',', $value);
                    foreach ($js_list as $js_filename) {
                        $tmp = trim($js_filename);
                        if ( ! empty($tmp) ) {
                            $this->Html->js($this->js_path . $tmp);
                        }
                    }
                    break;
                case 'js_lib': // @todo 
                    $js_libs = explode(',', $value);
                    foreach ($js_libs as $js_lib) {
                        $this->JsLib->request(trim($js_lib));
                    }
                    break;
                case 'icon':
                //case 'shortcut_icon':
                    if ( ! empty($value) ) {
                        $tmp = parse_url($value);
                        if (substr($tmp['path'], -4) == '.png') {
                            $type = 'image/png';
                        } elseif (substr($tmp['path'], -4) == '.gif') {
                            $type = 'image/gif';
                        } else {
                            $type = 'image/vnd.microsoft.icon';
                        }

                        $attr = array(
                            'rel' => 'icon',
                            //'rel' => 'shortcut icon',
                            'type' => $type,
                            //'_ie' => 'IE',
                        );

                        if (false !== strpos($value, '{IMG_PATH}')) {
                            $value = str_replace('{IMG_PATH}', $this->img_path, $value);
                        }

                        $this->Html->link($value, $attr);
                    }
                    break;
                default;
            }
        } // end foreach $this->ini
    }
}