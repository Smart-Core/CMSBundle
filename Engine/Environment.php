<?php

namespace SmartCore\Bundle\EngineBundle\Engine;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Environment extends ParameterBag
{
    /**
     * Constructor.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     */
    public function __construct(ContainerInterface $container)
    {
        $kernel    = $container->get('kernel');
        $base_path = $container->get('request')->getBasePath() . '/';

        // @todo сделать поддержку мультисайтовости.

        parent::__construct(array(
            'base_path'             => $base_path,
            'base_url'              => $container->get('request')->getBaseUrl() . '/',
            'current_folder_id'     => 1,
            'current_folder_path'   => $base_path,
            'dir_app'               => $kernel->getRootDir()  . '/',
            'dir_backup'            => $kernel->getRootDir()  . '/var/backup/',
            'dir_cache'             => $kernel->getCacheDir() . '/',
//            'dir_log'               => $kernel->getLogDir()   . '/', // !!! Сейчас нельзя тут вызывать $kernel->getLogDir() потому что образуется циклический вызов т.к. метод getLogDir() юзает 'engine.site', который в свою очередь юзает 'engine.env'
            'dir_var'               => $kernel->getRootDir()  . '/var/',
            'dir_tmp'               => $kernel->getRootDir()  . '/var/tmp/',
            // "site/" - если задано, то включается режим мультисайтовости и файлы берутся из подпапки ./site/{site_id}/
            'dir_sites'             => '',
            'dir_web_root'          => getcwd() . DIRECTORY_SEPARATOR,
            // Хост проекта, в формате "site.com" т.е. без префикса "www."
            'http_host'             => str_replace('www.', '', $_SERVER['HTTP_HOST']),
            // Относительный путь к теме оформления.
            'theme_path'            => 'theme/',
            // Путь к глобальным ресурсам. Может быть на другом домене, например 'http://site.com/assets/'
            'global_assets'         => $base_path . 'assets/',
        ));
    }    

    /**
     * Магическое получение параметров.
     *
     * @param string $name
     */
    public function __get($name)
    {
        return $this->get($name);
    }
}