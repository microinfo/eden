<?php //-->
/*
 * This file is part of the Eden package.
 * (c) 2009-2011 Christian Blanquera <cblanquera@gmail.com>
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Generates select query string syntax
 *
 * @package    Eden
 * @category   sql
 * @author     Christian Blanquera <cblanquera@gmail.com>
 * @version    $Id: select.php 1 2010-01-02 23:06:36Z blanquera $
 */
class Eden_Sql_Select extends Eden_Sql_Query {
	/* Constants
	-------------------------------*/
	/* Public Properties
	-------------------------------*/
	/* Protected Properties
	-------------------------------*/
	protected $_select 	= NULL;
	protected $_from 	= NULL;
	protected $_joins 	= NULL;
	protected $_where 	= array();
	protected $_sortBy	= array();
	protected $_group	= array();
	protected $_page 	= NULL;
	protected $_length	= NULL;
	
	/* Private Properties
	-------------------------------*/
	/* Get
	-------------------------------*/
	public static function get($select = '*') {
		return self::_getMultiple(__CLASS__, $select);
	}
	
	/* Magic
	-------------------------------*/
	public function __construct($select = '*') {
		$this->select($select);
	}
	
	/* Public Methods
	-------------------------------*/
	/**
	 * Select clause
	 *
	 * @param string select
	 * @return this
	 * @notes loads select phrase into registry
	 */
	public function select($select = '*') {
		//Argument 1 must be a string or array
		Eden_Sql_Error::get()->argument(1, 'string', 'array');
		
		//if select is an array
		if(is_array($select)) {
			//transform into a string
			$select = implode(', ', $select);
		}
		
		$this->_select = $select;
		
		return $this;
	}
	
	/**
	 * From clause
	 *
	 * @param string from
	 * @return this
	 * @notes loads from phrase into registry
	 */
	public function from($from) {
		//Argument 1 must be a string
		Eden_Sql_Error::get()->argument(1, 'string');
		
		$this->_from = $from;
		return $this;
	}
	
	/**
	 * Allows you to add joins of different types
	 * to the query 
	 *
	 * @param string type
	 * @param string table
	 * @param string where
	 * @param bool on
	 * @return this
	 * @notes loads join phrase into registry
	 */
	public function join($type, $table, $where, $using = true) {
		//argument test
		Eden_Sql_Error::get()
			->argument(1, 'string')		//Argument 1 must be a string
			->argument(2, 'string') 	//Argument 2 must be a string
			->argument(3, 'string') 	//Argument 3 must be a string
			->argument(4, 'bool'); 		//Argument 4 must be a boolean
		
		$linkage = $using ? 'USING ('.$where.')' : ' ON ('.$where.')';
		$this->_joins[] = $type.' JOIN ' . $table . ' ' . $linkage;
		
		return $this;
	}
	
	/**
	 * Inner join clause
	 *
	 * @param string table
	 * @param string where
	 * @param bool on
	 * @return this
	 * @notes loads inner join phrase into registry
	 */
	public function innerJoin($table, $where, $using = true) {
		//argument test
		Eden_Sql_Error::get()
			->argument(1, 'string')		//Argument 1 must be a string
			->argument(2, 'string') 	//Argument 2 must be a string
			->argument(3, 'bool'); 		//Argument 3 must be a boolean
		
		return $this->joined('INNER', $table, $where, $using);
	}
	
	/**
	 * Outer join clause
	 *
	 * @param string table
	 * @param string where
	 * @param bool on
	 * @return this
	 * @notes loads outer join phrase into registry
	 */
	public function outerJoin($table, $where, $using = true) {
		//argument test
		Eden_Sql_Error::get()
			->argument(1, 'string')		//Argument 1 must be a string
			->argument(2, 'string') 	//Argument 2 must be a string
			->argument(3, 'bool'); 		//Argument 3 must be a boolean
		
		return $this->joined('OUTER', $table, $where, $using);
	}
	
	/**
	 * Left join clause
	 *
	 * @param string table
	 * @param string where
	 * @param bool on
	 * @return this
	 * @notes loads left join phrase into registry
	 */
	public function leftJoin($table, $where, $using = true) {
		//argument test
		Eden_Sql_Error::get()
			->argument(1, 'string')		//Argument 1 must be a string
			->argument(2, 'string') 	//Argument 2 must be a string
			->argument(3, 'bool'); 		//Argument 3 must be a boolean
		
		return $this->joined('LEFT', $table, $where, $using);
	}
	
	/**
	 * Right join clause
	 *
	 * @param string table
	 * @param string where
	 * @param bool on
	 * @return this
	 * @notes loads right join phrase into registry
	 */
	public function rightJoin($table, $where, $using = true) {
		//argument test
		Eden_Sql_Error::get()
			->argument(1, 'string')		//Argument 1 must be a string
			->argument(2, 'string') 	//Argument 2 must be a string
			->argument(3, 'bool'); 		//Argument 3 must be a boolean
		
		return $this->joined('RIGHT', $table, $where, $using);
	}
	
	/**
	 * Where clause
	 *
	 * @param array|string where
	 * @return	this
	 * @notes loads a where phrase into registry
	 */
	public function where($where) {
		//Argument 1 must be a string or array
		Eden_Sql_Error::get()->argument(1, 'string', 'array');
		
		if(is_string($where)) {
			$where = array($where);
		}
		
		$this->_where = array_merge($this->_where, $where); 
		
		return $this;
	}
	
	/**
	 * Order by clause
	 *
	 * @param string field
	 * @param string order
	 * @return this
	 * @notes loads field and order into registry
	 */
	public function sortBy($field, $order = 'ASC') {
		//argument test
		Eden_Sql_Error::get()
			->argument(1, 'string')		//Argument 1 must be a string
			->argument(2, 'string'); 	//Argument 2 must be a string
		
		$this->_sortBy[] = $field . ' ' . $order;
		
		return $this;
	}
	
	/**
	 * Group by clause
	 *
	 * @param string group
	 * @return this
	 * @notes adds broup by functionality
	 */
	public function groupBy($group) {
		 //Argument 1 must be a string or array
		 Eden_Sql_Error::get()->argument(1, 'string', 'array');	
			
		if(is_string($group)) {
			$this->_group = array($group); 
		}
		
		$this->_group = $group; 
		return $this;
	}
	
	/**
	 * Limit clause
	 *
	 * @param string|int page
	 * @param string|int length
	 * @return this
	 * @notes loads page and length into registry
	 */
	public function limit($page, $length) {
		//argument test
		Eden_Sql_Error::get()
			->argument(1, 'numeric')	//Argument 1 must be a number
			->argument(2, 'numeric');	//Argument 2 must be a number
		
		$this->_page = $page;
		$this->_length = $length; 

		return $this;
	}
	
	/**
	 * Returns the string version of the query 
	 *
	 * @param  bool
	 * @return string
	 * @notes returns the query based on the registry
	 */
	public function getQuery() {
		$joins = empty($this->_joins) ? '' : implode(' ', $this->_joins);
		$where = empty($this->_where) ? '' : 'WHERE '.implode(' AND ', $this->_where);
		$sort = empty($this->_sortBy) ? '' : 'ORDER BY '.implode(', ', $this->_sortBy);
		$limit = is_null($this->_page) ? '' : 'LIMIT ' . $this->_page .',' .$this->_length;
		$group = empty($this->_group) ? '' : 'GROUP BY ' . implode(', ', $this->_group);
		
		$query = sprintf(
			'SELECT %s FROM %s %s %s %s %s %s;',
			$this->_select, $this->_from, $joins,
			$where, $sort, $limit, $group);
		
		return str_replace('  ', ' ', $query);
	}
	
	/* Protected Methods
	-------------------------------*/
	/* Private Methods
	-------------------------------*/
}