<?php

namespace Gesdon\Core;

use Gesdon\Extension;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Controller
{
    private $application = null;
    
    public function __construct()
    {
        $this->application = new Application();
        
        $this->configure();
    }
    
    
    public function run()
    {
        return $this->application->run();
    }
    
    
    private function configure()
    {
        // Parameter application
        $this->application['debug'] = true;
        
        // Twig
        $this->configureTwig();
        
        // Form
        $this->configureForm();
        
        // URL Generator
        $this->configureUrl();
        
        // Sessions
        $this->configureSession();
        
        // Load routing file
        $routing_file = Config::get('config_dir').DIRECTORY_SEPARATOR.'routing.php';
        if (file_exists($routing_file) === false) {
            throw new Exception('Impossible to load routing file');
        }
        
        require_once $routing_file;
        
        $self = $this;
        
        foreach ($routing as $route => $params) {
            $this->application->get($route, function() use ($self, $params) {
                $class_name = '\\Gesdon\\App\\'.$params['class'];
                $class = new $class_name($self->getApplication());
                return $class->executeGet();
            })->bind($params['name']);
            
            $this->application->post($route, function() use ($self, $params) {
                $class_name = '\\Gesdon\\App\\'.$params['class'];
                $class = new $class_name($self->getApplication());
                return $class->executePost();
            })->bind($params['name'].'_save');
            
            $this->application->put($route, function() use ($self, $params) {
                $class_name = '\\Gesdon\\App\\'.$params['class'];
                $class = new $class_name($self->getApplication());
                return $class->executePut();
            })->bind($params['name'].'_add');
            
            $this->application->delete($route, function() use ($self, $params) {
                $class_name = '\\Gesdon\\App\\'.$params['class'];
                $class = new $class_name($self->getApplication());
                return $class->executeDelete();
            })->bind($params['name'].'_delete');
        }
    }
    
    private function configureTwig()
    {
        $this->application->register(new \Silex\Provider\TwigServiceProvider(), array(
            'twig.path' => Config::get('template_dir'),
        ));
        
        $this->application['twig'] = $this->application->share($this->application->extend('twig', function($twig, $app) {
            $twig->addExtension(new Extension\Twig\Asset());
            $twig->addExtension(new Extension\Twig\Config());
            $twig->addExtension(new Extension\Twig\Format());
            return $twig;
        }));
    }
    
    private function configureForm()
    {
        $this->application->register(new \Silex\Provider\FormServiceProvider(), array(
            'form.secret' => Config::get('csrf_secret', sha1(__DIR__.php_uname()))
        ));
        
        $this->application->register(new \Silex\Provider\ValidatorServiceProvider());
        $this->application->register(new \Silex\Provider\TranslationServiceProvider(), array(
            'translator.messages' => array(),
        ));
    }
    
    
    private function configureUrl()
    {
        $this->application->register(new \Silex\Provider\UrlGeneratorServiceProvider());
    }
    
    
    private function configureSession()
    {
        $this->application->register(new \Silex\Provider\SessionServiceProvider(), array(
            
        ));
    }
    
    public function getApplication()
    {
        return $this->application;
    }
}