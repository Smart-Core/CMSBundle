<?php
/**
 * Обёртка для Doctrine DBAL.
 */
namespace SmartCore\Bundle\EngineBundle\Engine;

class DataBaseWrapper extends \Doctrine\DBAL\Connection
{
	/**
	 * Префикс таблиц
	 * @var string
	 */
	private $prefix = '';

	/**
	 * Получить значение префикса таблиц.
	 *
	 * @return string
	 */
	public function prefix()
	{
		return $this->prefix;
	}
	
	/**
	 * Установить префикс таблиц.
	 *
	 * @param string $prefix
	 */
	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;
	}	
	
	/**
	 * Prepares and executes an SQL query and returns the first row of the result
	 * as a object.
	 *
	 * @param string $statement   SQL query to be executed
	 * @param array $params       prepared statement params
	 * @return object
	 */
	public function fetchObject($statement, array $params = array())
	{
		return $this->executeQuery($statement, $params)->fetch(\PDO::FETCH_OBJ);
	}	
}