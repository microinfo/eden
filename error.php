<?php //-->
/*
 * This file is part of the Eden package.
 * (c) 2010-2012 Christian Blanquera <cblanquera@gmail.com>
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

require_once dirname(__FILE__).'/event.php';
require_once dirname(__FILE__).'/error/event.php';

/*
 * This file is part of the Eden package.
 * (c) 2010-2012 Christian Blanquera <cblanquera@gmail.com>
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * The base class for any class handling exceptions. Exceptions
 * allow an application to custom handle errors that would 
 * normally let the system handle. This exception allows you to 
 * specify error levels and error types. Also using this exception
 * outputs a trace (can be turned off) that shows where the problem
 * started to where the program stopped.
 *
 * @package    Eden
 * @category   error
 * @author     Christian Blanquera <cblanquera@gmail.com>
 * @version    $Id: exception.php 1 2010-01-02 23:06:36Z blanquera $
 */
class Eden_Error extends Exception {
	/* Constants
	-------------------------------*/
	const REFLECTION_ERROR 		= 'Error creating Reflection Class: %s, Method: %s.';
	const INVALID_ARGUMENT 		= 'Argument %d in %s() was expecting %s, however %s was given.';
	
	//error type
	const ARGUMENT	= 'ARGUMENT';	//used when argument is invalidated
	const LOGIC 	= 'LOGIC'; 		//used when logic is invalidated
	const GENERAL 	= 'GENERAL'; 	//used when anything in general is invalidated
	const CRITICAL 	= 'CRITICAL'; 	//used when anything caused application to crash
	
	//error level
	const WARNING 		= 'WARNING';
	const ERROR 		= 'ERROR';
	const DEBUG 		= 'DEBUG'; 			//used for temporary developer output
	const INFORMATION 	= 'INFORMATION'; 	//used for permanent developer notes
	
	
	/* Public Properties
	-------------------------------*/
	/* Protected Properties
	-------------------------------*/
	protected $_reporter	= NULL;
	protected $_type 		= NULL;
	protected $_level 		= NULL;
	protected $_offset		= 1;
	protected $_variables 	= array();
	
	/* Private Properties
	-------------------------------*/
	/* Get
	-------------------------------*/
	public static function get($message = NULL, $code = 0) {
		$class = __CLASS__;
		return new $class($message, $code);
	}
	
	/* Magic
	-------------------------------*/
    public function __construct($message = NULL, $code = 0) {
		$this->_type = self::LOGIC;
		$this->_level = self::ERROR;
		$this->_reporter = $this->_getReporter();
		parent::__construct($message, $code);
	}
	
	/* Public Methods
	-------------------------------*/
	/**
	 * Sets message
	 *
	 * @param string
	 * @return this
	 */
	public function setMessage($message) {
		$this->message = $message;
		return $this;
	}
	
	/**
	 * Sets what index the trace should start at
	 *
	 * @return this
	 */
	public function setTraceOffset($offset) {
		$this->_offset = $offset;
		return $this;
	}
	
	/**
	 * Returns the trace offset; where we should start the trace
	 *
	 * @return this
	 */
	public function getTraceOffset() {
		return $this->_offset;
	}
	
	/**
	 * Sets exception level
	 *
	 * @param string
	 * @return this
	 */
	public function setLevel($level) {
		$this->_level = $level;
		return $this;
	}
	
	/**
	 * Sets exception level to WARNING
	 *
	 * @return this
	 */
	public function setLevelWarning() {
		return $this->setLevel(self::WARNING);
	}
	
	/**
	 * Sets exception level to ERROR
	 *
	 * @return this
	 */
	public function setLevelError() {
		return $this->setLevel(self::WARNING);
	}
	
	/**
	 * Sets exception level to DEBUG
	 *
	 * @return this
	 */
	public function setLevelDebug() {
		return $this->setLevel(self::DEBUG);
	}
	
	/**
	 * Sets exception level to INFORMATION
	 *
	 * @return this
	 */
	public function setLevelInformation() {
		return $this->setLevel(self::INFORMATION);
	}
	
	/**
	 * Returns the exception level
	 *
	 * @return string
	 */
	public function getLevel() {
		return $this->_level;
	}
	
	/**
	 * Sets exception type
	 *
	 * @param string
	 * @return this
	 */
	public function setType($type) {
		$this->_type = $type;
		return $this;
	}
	
	/**
	 * Sets exception type to ARGUMENT
	 *
	 * @return this
	 */
	public function setTypeArgument() {
		return $this->setType(self::ARGUMENT);
	}
	
	/**
	 * Sets exception type to LOGIC
	 *
	 * @return this
	 */
	public function setTypeLogic() {
		return $this->setType(self::CRITICAL);
	}
	
	/**
	 * Sets exception type to GENERAL
	 *
	 * @return this
	 */
	public function setTypeGeneral() {
		return $this->setType(self::GENERAL);
	}
	
	/**
	 * Sets exception type to CRITICAL
	 *
	 * @return this
	 */
	public function setTypeCritical() {
		return $this->setType(self::CRITICAL);
	}
	
	/**
	 * Returns the exception type
	 *
	 * @return string
	 */
	public function getType() {
		return $this->_type;
	}
	
	/**
	 * REturns the class or method that caught this
	 *
	 * @return string
	 */
	public function getReporter() {
		return $this->_reporter;
	}
	
	/**
	 * Adds parameters used in the message
	 *
	 * @return this
	 */
	public function addVariable($variable) {
		$this->_variables[] = $variable;
		return $this;
	}
	
	/**
	 * Combines parameters with message and throws it
	 *
	 * @return void
	 */
	public function trigger() {
		if(!empty($this->_variables)) {
			$this->message = vsprintf($this->message, $this->_variables);
		}
		
		throw $this;
	}
	
	/**
	 * Tests arguments for valid data types
	 *
	 * @param *int
	 * @param *mixed
	 * @param *string[,string..]
	 * @return this
	 */
	public function argument($index, $types) {
		$trace 		= debug_backtrace();
		$trace 		= $trace[1];
		$types 		= func_get_args();
		$index 		= array_shift($types) - 1;
		
		if($index < 0) {
			$index = 0;
		}
		
		//if it's not set then it's good because the default value
		//set in the method will be it.
		if($index >= count($trace['args'])) {
			return $this;
		}
		
		$argument 	= $trace['args'][$index];
		
		foreach($types as $i => $type) {
			$method = 'is_'.$type;
			if(function_exists($method) && $method($argument)) {
				return $this;
			}
			
			if(class_exists($type) && $argument instanceof $type) {
				return $this;
			}
		}
		
		//lets formulate the method
		$method = $trace['function'];
		if(isset($trace['class'])) {
			$method = $trace['class'].'->'.$method;
		}
		
		$type = 'unknown';
		if(is_string($argument)) {
			$type = "'".$argument."'";
		} else if(is_numeric($argument)) {
			$type = $argument;
		} else if(is_array($argument)) {
			$type = 'Array';
		} else if(is_bool($argument)) {
			$type = $argument ? 'true' : 'false';
		} else if(is_object($argument)) {
			$type = get_class($argument);
		} else if(is_null($argument)) {
			$type = 'null';
		}
		
		$this->setMessage(self::INVALID_ARGUMENT)
			->addVariable($index + 1)
			->addVariable($method)
			->addVariable(implode(' or ', $types))
			->addVariable($type)
			->setTypeLogic()
			->setTraceOffset(2)
			->trigger();
	}
	
	/* Protected Methods
	-------------------------------*/
	protected function _getReporter() {
		$trace = $this->getTrace();
		
		if(isset($trace[0]['class'])) {
			return $trace[0]['class'];
		}
		
		return get_class($this);
	}
	
	/* Private Methods
	-------------------------------*/
}