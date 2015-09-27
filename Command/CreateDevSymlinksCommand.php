<?php

namespace SmartCore\Bundle\CMSBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
//use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;

class CreateDevSymlinksCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cms:create:dev:symlinks')
            ->setDescription('Create symlinks to cms-dev.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cmsDevDir = $this->getContainer()->getParameter('kernel.root_dir').'/../cms-dev';

        $filesystem = $this->getContainer()->get('filesystem');

        $filesystem->mkdir($cmsDevDir.'/SmartCore/Bundle', 0777);
        $filesystem->mkdir($cmsDevDir.'/SmartCore/Module', 0777);

        $bundles = [
            'cms-bundle' => 'CMSBundle',
            'cms-generator-bundle' => 'CMSGeneratorBundle',
            'felib-bundle' => 'FelibBundle',
            'html-bundle' => 'HtmlBundle',
            'media-bundle' => 'MediaBundle',
        ];

        $modules = [
            'module-breadcrumbs' => 'Breadcrumbs',
            'module-gallery' => 'Gallery',
            'module-slider' => 'Slider',
            'module-menu' => 'Menu',
            'module-texter' => 'Texter',
            'module-unicat' => 'Unicat',
            'module-user' => 'User',
            'module-webform' => 'WebForm',
            'module-widget' => 'Widget',
        ];

        foreach ($modules as $moduleId => $moduleName) {
            $targetDir = $cmsDevDir.'/SmartCore/Module/'.$moduleName;

            $filesystem->remove($targetDir);

            $filesystem->symlink(realpath($this->getContainer()->getParameter('kernel.root_dir').'/../vendor/smart-core/'.$moduleId), $targetDir);

            if (!file_exists($targetDir)) {
                throw new IOException('Symbolic link is broken');
            }
        }

        foreach ($bundles as $bundleId => $bundleName) {
            $targetDir = $cmsDevDir.'/SmartCore/Bundle/'.$bundleName;

            $filesystem->remove($targetDir);

            $filesystem->symlink(realpath($this->getContainer()->getParameter('kernel.root_dir').'/../vendor/smart-core/'.$bundleId), $targetDir);

            if (!file_exists($targetDir)) {
                throw new IOException('Symbolic link is broken');
            }
        }
    }
}
