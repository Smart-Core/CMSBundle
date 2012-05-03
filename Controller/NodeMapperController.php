<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class NodeMapperController extends Controller
{
	public function indexAction($slug)
	{
		$this->init();

		$data = array();
		$sql = "SELECT * FROM text_items ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$data[$row->item_id] = $row->text;
		}
		
		cmf_dump($data);
//		cmf_dump($this->getUser());
		
		return new Response("Hello $slug !");
	}
}