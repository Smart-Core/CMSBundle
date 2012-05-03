<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class NodeMapperController extends Controller
{
	
	public function indexAction($slug)
	{
		
		$this->DB = $this->get('db');
		
		$data = array();
		
		$sql = "SELECT * FROM text_items ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$data[$row->item_id] = $row->text;
		}
		
		cmf_dump($data);
		
		return new Response("Hello $slug !");
	}
}
