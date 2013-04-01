<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
//use Symfony\Component\HttpFoundation\Response;
use SmartCore\Bundle\EngineBundle\Controller\Controller;
use SmartCore\Bundle\EngineBundle\Entity\Node;

class HelloController extends Controller
{    
    public function indexAction()
    {
        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $this->get('html')
            ->doctype('xhtml')
            ->lang('en')
            ->title('hi :)')
        ;

//        $nodes = $em->getRepository('SmartCoreEngineBundle:Node')->findInInheritanceFolder($folder_id);

//        $b = $em->getRepository('SmartCoreEngineBundle:Block')->find(2);
//        $folder = $em->find('SmartCoreEngineBundle:Folder', 1);
//        $b->setFolders($folder);
//        ld($b->getFolders());

//        $em->persist($folder);
//        $em->flush();

//        $repo = $em->getRepository('SmartCoreEngineBundle:Node')->findIn(array(4, 6, 5, 1));
//        $node = $em->find('SmartCoreEngineBundle:Node', 8);
//        ld($repo);

        /** @var $node Node */
        //ld($this->get('engine.folder')->getUri($node->getFolder()->getId()));

        return $this->render('HtmlBundle::test.html.twig', array('hello' => 'Hello World!'));
    }
}
