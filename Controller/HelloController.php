<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Doctrine\ORM\Query\ResultSetMapping;
//use Symfony\Component\HttpFoundation\Response;
use SmartCore\Bundle\EngineBundle\Controller\Controller;

class HelloController extends Controller
{    
    public function indexAction()
    {
        $em = $this->EM();

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

//        $node = $em->find('SmartCoreEngineBundle:Node', 1);
//        file_put_contents('e:/node_1', serialize($node));

        $node = unserialize(file_get_contents('e:/node_1'));

//        ld($node->getId());
//        ld($node->getFolder()->getTitle());

        return $this->render('HtmlBundle::test.html.twig', array('hello' => 'Hello World!'));
    }
}
