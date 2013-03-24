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
        /*
        $sql = "SELECT n.*
                FROM aaa_engine_nodes AS n,
                    engine_blocks_inherit AS bi
                WHERE n.block_id = bi.block_id
                    AND is_active = 1
                    AND n.folder_id = '1'
                    AND bi.folder_id = '1'
                ORDER BY n.position
            ";

        $db = $this->container->get('engine.db');
        $result = $db->query($sql);

        while ($row = $result->fetchObject()) {
//            ld($row);
        }

        /*
        $folder = $em->find('SmartCoreEngineBundle:Folder', 1);
        $query = $em->createQuery('
            SELECT n
            FROM SmartCoreEngineBundle:Node n, SmartCoreEngineBundle:Block b
            WHERE n.is_active = 1
            AND n.block = b.block_id
            AND n.folder = :folder
            ORDER BY n.position ASC'
        )->setParameter('folder', $folder);

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('SmartCoreEngineBundle:Node', 'n');
        $rsm->addFieldResult('n', 'node_id', 'node_id');
//        $rsm->addFieldResult('n', 'folder_id', 'folder_id');
//        $rsm->addFieldResult('n', 'block', 'block');

        $query = $em->createNativeQuery('
            SELECT node_id
            FROM aaa_engine_nodes
        ', $rsm);

        /*
            WHERE n.is_active = 1
        $query = $em->createNativeQuery('
            SELECT n.*
            FROM aaa_engine_nodes AS n,
                engine_blocks_inherit AS bi
            WHERE n.block_id = bi.block_id
                AND n.is_active = 1
                AND n.folder_id = 1
                AND bi.folder_id = 1
            ORDER BY n.position
            ', $rsm);
        */
        //$query->setParameter(1, 'romanb');

//        $nodes = $query->getResult();


//        ld($nodes);


        //$nodes = $em->getRepository('SmartCoreEngineBundle:Node')->findInInheritanceFolder($folder_id);

//        $b = $em->getRepository('SmartCoreEngineBundle:Block')->find(2);

//        $folder = $em->find('SmartCoreEngineBundle:Folder', 1);
//        $b->setFolders($folder);
//        ld($b->getFolders());

//        $em->persist($folder);
//        $em->flush();


//        $repo = $em->getRepository('SmartCoreEngineBundle:Folder');

//        ld($repo);

//        $folder = $em->find('SmartCoreEngineBundle:Folder', 1);

//        $children = $folder->getChildren();
//        $nodes = $folder->getNodes();

//        foreach ($nodes as $child) {
//            ld($child);
//        }

        /** @var $node Node */
//        $node = $em->find('SmartCoreEngineBundle:Node', 2);
//        file_put_contents('e:/node_1ig', serialize($node));
//        $node = unserialize(file_get_contents('e:/node_1'));
//        $node = unserialize(file_get_contents('e:/node_1ig'));

//        ld($node->getId());
//        ld($node->getFolder()->getTitle());
//        ld($node->getBlock());

//        ld($node->getFolderId());
        /*
        $query = $em->createQuery('
            SELECT n
            FROM SmartCoreEngineBundle:Node n
            WHERE n.node_id IN(5,4,3,2,1)
            ORDER BY n.position DESC
        ');
        */
//        $nodes = $query->getResult();

//        ld($node);

        return $this->render('HtmlBundle::test.html.twig', array('hello' => 'Hello World!'));
    }
}
