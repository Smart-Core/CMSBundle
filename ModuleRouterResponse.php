<?php

namespace SmartCore\Bundle\EngineBundle;

class ModuleRouterResponse extends Response
{
    protected $controller = null;
    protected $action = null;
    protected $arguments = array();
    protected $breadcrumbs = array();

    /**
     * Constructor.
     *
     * @param string  $content The response content
     * @param integer $status  The response status code
     * @param array   $headers An array of response headers
     *
     * @api
     */
    public function __construct($content = '', $status = 200, $headers = array())
    {
        parent::__construct($content, 404, $headers);
    }

    public function getController()
    {
        return $this->controller;
    }

    public function setController($name)
    {
        $this->setStatusCode(200);
        $this->controller = $name;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setAction($name)
    {
        $this->setStatusCode(200);
        $this->action = $name;
    }

    public function getArgument($name)
    {
        return $this->arguments[$name];
    }

    public function getAllArguments()
    {
        return $this->arguments;
    }

    public function setArgument($name, $value)
    {
        $this->setStatusCode(200);
        $this->arguments[$name] = $value;
    }

    public function addBreacrumb($uri, $title, $descr = false)
    {
        $this->breadcrumbs[] = array(
            'uri'   => $uri,
            'title' => $title,
            'descr' => $descr,
        );
    }

    public function getBreadcrumbs()
    {
        return $this->breadcrumbs;
    }
}