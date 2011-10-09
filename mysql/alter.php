<?php //-->
/*
 * This file is part of the Eden package.
 * (c) 2009-2011 Christian Blanquera <cblanquera@gmail.com>
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Generates alter query string syntax
 *
 * @package    Eden
 * @category   sql
 * @author     Christian Blanquera <cblanquera@gmail.com>
 * @version    $Id: alter.php 1 2010-01-02 23:06:36Z blanquera $
 */
class Eden_Mysql_Alter extends Eden_Sql_Query {
	/* Constants
	-------------------------------*/
	/* Public Properties
	-------------------------------*/
	/* Protected Properties
	-------------------------------*/
	protected $_name 				= NULL;
	protected $_changeFields 		= array();
	protected $_addFields 			= array();
	protected $_removeFields 		= array();
	protected $_addKeys 			= array();
	protected $_removeKeys 			= array();
	protected $_addUniqueKeys 		= array();
	protected $_removeUniqueKeys 	= array();
	protected $_addPrimaryKeys 		= array();
	protected $_removePrimaryKeys 	= array();
	
	/* Private Properties
	-------------------------------*/
	/* Get
	-------------------------------*/
	public static function get($name = NULL) {
		return self::_getMultiple(__CLASS__, $name);
	}
	
	/* Magic
	-------------------------------*/
	public function __construct($name = NULL) {
		if(is_string($name)) {
			$this->setName($name);
		}
	}
	
	/* Public Methods
	-------------------------------*/
	/**
	 * Sets the name of the table you wish to create
	 *
	 * @param string name
	 * @return this
	 */
	public function setName($name) {
		//Argument 1 must be a string
		Eden_Mysql_Error::get()->argument(1, 'string');
		
		$this->_name = $name;
		return $this;
	}
	
	/**
	 * Changes attributes of the table given 
	 * the field name
	 *
	 * @param string name
	 * @param array attributes
	 * @return this
	 */
	public function changeField($name, array $attributes) {
		//Argument 1 must be a string
		Eden_Mysql_Error::get()->argument(1, 'string');
		
		$this->_changeFields[$name] = $attributes;
		return $this;
	}
	
	/**
	 * Adds a field in the table
	 *
	 * @param string name
	 * @param array attributes
	 * @return this
	 */
	public function addField($name, array $attributes) {
		//Argument 1 must be a string
		Eden_Mysql_Error::get()->argument(1, 'string');
		
		$this->_addFields[$name] = $attributes;
		return $this;
	}
	
	/**
	 * Removes a field
	 *
	 * @param string name
	 * @return this
	 */
	public function removeField($name) {
		//Argument 1 must be a string
		Eden_Mysql_Error::get()->argument(1, 'string');
		
		$this->_removeFields[] = $name;
		return $this;
	}
	
	/**
	 * Adds an index key
	 *
	 * @param string name
	 * @return this
	 */
	public function addKey($name) {
		//Argument 1 must be a string
		Eden_Mysql_Error::get()->argument(1, 'string');
		
		$this->_addKeys[] = '`'.$name.'`';
		return $this;
	}
	
	/**
	 * Removes an index key
	 *
	 * @param string name
	 * @return this
	 */
	public function removeKey($name) {
		//Argument 1 must be a string
		Eden_Mysql_Error::get()->argument(1, 'string');
		
		$this->_removeKeys[] = $name;
		return $this;
	}
	
	/**
	 * Adds a unique key
	 *
	 * @param string name
	 * @return this
	 */
	public function addUniqueKey($name) {
		//Argument 1 must be a string
		Eden_Mysql_Error::get()->argument(1, 'string');
		
		$this->_addUniqueKeys[] = '`'.$name.'`';
		return $this;
	}
	
	/**
	 * Removes a unique key
	 *
	 * @param string name
	 * @return this
	 */
	public function removeUniqueKey($name) {
		//Argument 1 must be a string
		Eden_Mysql_Error::get()->argument(1, 'string');
		
		$this->_removeUniqueKeys[] = $name;
		return $this;
	}
	
	/**
	 * Adds a primary key
	 *
	 * @param string name
	 * @return this
	 */
	public function addPrimaryKey($name) {
		//Argument 1 must be a string
		Eden_Mysql_Error::get()->argument(1, 'string');
		
		$this->_addPrimaryKeys[] = '`'.$name.'`';
		return $this;
	}
	
	/**
	 * Removes a primary key
	 *
	 * @param string name
	 * @return this
	 */
	public function removePrimaryKey($name) {
		//Argument 1 must be a string
		Eden_Mysql_Error::get()->argument(1, 'string');
		
		$this->_removePrimaryKeys[] = $name;
		return $this;
	}
	
	/**
	 * Returns the string version of the query 
	 *
	 * @param  bool
	 * @return string
	 * @notes returns the query based on the registry
	 */
	public function getQuery($unbind = false) {	
		$fields = array();
		$table = '`'.$this->_name.'`';
		
		foreach($this->_removeFields as $name) {
			$fields[] = 'DROP `'.$name.'`';
		}
		
		foreach($this->_addFields as $name => $attr) {
			$field = array('ADD `'.$name.'`');
			if(isset($attr['type'])) {	
				$field[] = isset($attr['length']) ? $attr['type'] . '('.$attr['length'].')' : $attr['type'];
			}
			
			if(isset($attr['attribute'])) {
				$field[] = $attr['attribute'];
			}
			
			if(isset($attr['null'])) {
				if($attr['null'] == false) {
					$field[] = 'NOT NULL';
				} else {
					$field[] = 'DEFAULT NULL';
				}
			}
			
			if(isset($attr['default'])&& $attr['default'] !== false) {
				if(!isset($attr['null']) || $attr['null'] == false) {
					if(is_string($attr['default'])) {
						$field[] = 'DEFAULT \''.$attr['default'] . '\'';
					} else if(is_numeric($attr['default'])) {
						$field[] = 'DEFAULT '.$attr['default'];
					}
				}
			}
			
			if(isset($attr['auto_increment']) && $attr['auto_increment'] == true) {
				$field[] = 'auto_increment';
			}
			
			$fields[] = implode(' ', $field);
		}
		
		foreach($this->_changeFields as $name => $attr) {
			$field = array('CHANGE `'.$name.'`  `'.$name.'`');
			
			if(isset($attr['name'])) {	
				$field = array('CHANGE `'.$name.'`  `'.$attr['name'].'`');
			}
			
			if(isset($attr['type'])) {	
				$field[] = isset($attr['length']) ? $attr['type'] . '('.$attr['length'].')' : $attr['type'];
			}
			
			if(isset($attr['attribute'])) {
				$field[] = $attr['attribute'];
			}
			
			if(isset($attr['null'])) {
				if($attr['null'] == false) {
					$field[] = 'NOT NULL';
				} else {
					$field[] = 'DEFAULT NULL';
				}
			}
			
			if(isset($attr['default'])&& $attr['default'] !== false) {
				if(!isset($attr['null']) || $attr['null'] == false) {
					if(is_string($attr['default'])) {
						$field[] = 'DEFAULT \''.$attr['default'] . '\'';
					} else if(is_numeric($attr['default'])) {
						$field[] = 'DEFAULT '.$attr['default'];
					}
				}
			}
			
			if(isset($attr['auto_increment']) && $attr['auto_increment'] == true) {
				$field[] = 'auto_increment';
			}
			
			$fields[] = implode(' ', $field);
		}
		
		foreach($this->_removeKeys as $key) {
			$fields[] = 'DROP INDEX `'.$key.'`';
		}
		
		if(!empty($this->_addKeys)) {
			$fields[] = 'ADD INDEX ('.implode(', ', $this->_addKeys).')';
		}
		
		foreach($this->_removeUniqueKeys as $key) {
			$fields[] = 'DROP INDEX `'.$key.'`';
		}
		
		if(!empty($this->_addUniqueKeys)) {
			$fields[] = 'ADD UNIQUE ('.implode(', ', $this->_addUniqueKeys).')';
		}
		
		foreach($this->_removePrimaryKeys as $key) {
			$fields[] = 'DROP PRIMARY KEY `'.$key.'`';
		}
		
		if(!empty($this->_addPrimaryKeys)) {
			$fields[] = 'ADD PRIMARY KEY ('.implode(', ', $this->_addPrimaryKeys).')';
		}
		
		$fields = implode(", \n", $fields);
		
		return sprintf(
			'ALTER TABLE %s %s;',
			$table, $fields);
	}
	
	/* Protected Methods
	-------------------------------*/
	/* Private Methods
	-------------------------------*/
}