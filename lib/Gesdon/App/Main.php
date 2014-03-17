<?php

namespace Gesdon\App;

use Gesdon\Core\Exception;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class Main
{
    private $app;
    protected $request;
    protected $response;
    
    protected $twig;
    protected $form;
    protected $url;
    protected $session;

    protected $current_database;
    
    public function __construct(\Silex\Application $app)
    {
        $this->app = $app;
        
        // Get the request and the response
        $this->request = $this->app['request'];
        $this->response = new Response();
        
        // Get the twig Object
        $this->twig = $this->app['twig'];
        
        // Get the form Object
        $this->form = $this->app['form.factory'];
        
        // Get the URL
        $this->url = $this->app['url_generator'];
        
        // Get the session
        $this->session = $this->app['session'];

        // Get the current database
        $propel_config = \Propel::getConfiguration();
        if (preg_match('/dbname=([^;]+);?/', $propel_config['datasources']['gesdon']['connection']['dsn'], $matches) === 1) {
            $this->current_database = $matches[1];
        } else {
            $this->current_database = null;
        }
    }
    
    abstract public function executeGet();
    
    public function executePost()
    {
        throw new Exception('The method POST isn\'t implemented');
    }
    
    
    public function executePut()
    {
        throw new Exception('The method PUT isn\'t implemented');
    }
    
    public function executeDelete()
    {
        throw new Exception('The method GET isn\'t implemented');
    }
    
    public function getApp()
    {
        return $this->app;
    }
    
    protected function render($params = array())
    {
        $params = array_merge(array('url' => $this->url, 'current_database' => $this->current_database), $params);
        
        $template = $this->getClassname().'/'.$this->getMethodName();
        return $this->twig->render($template, $params);
    }
    
    
    /**
     * Get the class name without namespace
     *
     * @return  string      the class name without namespace
     * @access  private
     */
    private function getClassname()
    {
        $class_name = get_class($this);
        return str_replace(array(__NAMESPACE__, '\\'), '', $class_name);
    }
    
    
    private function getMethodName()
    {
        $backtrace = debug_backtrace();
        
        // the called method, is the two last call method
        return strtolower(str_replace('execute', '', $backtrace[2]['function'])).'.html.twig';
    }
}