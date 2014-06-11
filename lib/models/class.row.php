<?php

class Row
{

	private $type, $name, $null, $default, $auto_increment, $primary, $comment, $references;
	private $length, $unsigned, $zerofill, $binary, $ascii, $unicode, $enum, $set;

	public function __construct($_name, $_type)
	{
		$this->type = $_type;
		$this->name = $_name;
	}

	/**
	 * Get row type
	 * @return mixed
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Returns all possible row types
	 */
	public static function getRowTypes()
	{
		return array('TINYINT', 'SMALLINT', 'MEDIUMINT', 'INT', 'INTEGER', 'BIGINT', 'REAL', 'DOUBLE', 'FLOAT', 'DECIMAL', 'NUMERIC', 'DATE', 'TIME', 'TIMESTAMP', 'DATETIME', 'CHAR', 'VARCHAR', 'TINYBLOB', 'BLOB', 'MEDIUMBLOB', 'LONGBLOB', 'TINYTEXT', 'TEXT', 'MEDIUMTEXT', 'LONGTEXT', 'ENUM', 'SET');
	}

	/**
	 * Format a value given a row type
	 * @param $type
	 * @param $value
	 * @return float|int
	 */
	static public function formatValue($type, $value)
	{
		switch ($type)
		{
			case 'TINYINT':
			case 'SMALLINT':
			case 'MEDIUMINT':
			case 'INT':
			case 'INTEGER':
			case 'BIGINT':
				$value = $value === null ? 'NULL' : intval($value);
				break;
			case 'REAL':
			case 'DOUBLE':
			case 'FLOAT':
			case 'DECIMAL':
			case 'NUMERIC':
				$value = $value === null ? 'NULL' : floatval($value);
				break;
			case 'DATE':
			case 'TIME':
			case 'TIMESTAMP':
			case 'DATETIME':
			case 'CHAR':
			case 'VARCHAR':
			case 'TINYBLOB':
			case 'BLOB':
			case 'MEDIUMBLOB':
			case 'LONGBLOB':
			case 'TINYTEXT':
			case 'TEXT':
			case 'MEDIUMTEXT':
			case 'LONGTEXT':
			case 'ENUM':
			case 'SET':
				$value = '"' . addslashes($value === null ? '' : $value) . '"';
				break;
		}
		return $value;
	}

	/**
	 * Get SQL row definition query
	 */
	public function getDefinitionQuery()
	{
		return $this->getName() . ' ' . $this->getType()
		. ($this->getLength() != '' ? '(' . $this->getLength() . ')' : ($this->getType() == RowType::VARCHAR ? '(100)' : ''))
		. (trim($this->getEnum()) != '' ? '(' . $this->getEnum() . ')' : '')
		. (trim($this->getSet()) != '' ? '(' . $this->getSet() . ')' : '')
		. ($this->getZerofill() ? ' ZEROFILL' : '')
		. ($this->getUnsigned() ? ' UNSIGNED' : '')
		. ($this->getBinary() ? ' BINARY' : '')
		. ($this->getAscii() ? ' ASCII' : '')
		. ($this->getUnicode() ? ' UNICODE' : '')
		. ($this->getNull() ? '' : ' NOT NULL')
		. ($this->getDefault() != '' ? ' DEFAULT ' . static::formatValue($this->getDefault(), $this->getDefault()) : '')
		. ($this->getAutoIncrement() ? ' AUTO_INCREMENT' : '')
		. ($this->getPrimary() ? ' PRIMARY KEY' : '')
		. ($this->getComment() != '' ? ' COMMENT "' . addslashes($this->getComment()) . '"' : '');
	}

	/*************************
	 * GETTERS AND SETTERS
	 *************************/

	public function setLength($val)
	{
		$this->length = $val;
	}

	public function getLength()
	{
		return $this->length;
	}

	public function setUnsigned($val)
	{
		$this->unsigned = $val;
	}

	public function getUnsigned()
	{
		return $this->unsigned;
	}

	public function setZerofill($val)
	{
		$this->zerofill = $val;
	}

	public function getZerofill()
	{
		return $this->zerofill;
	}

	public function setBinary($val)
	{
		$this->binary = $val;
	}

	public function getBinary()
	{
		return $this->binary;
	}

	public function setAscii($val)
	{
		$this->ascii = $val;
	}

	public function getAscii()
	{
		return $this->ascii;
	}

	public function setUnicode($val)
	{
		$this->unicode = $val;
	}

	public function getUnicode()
	{
		return $this->unicode;
	}

	public function getAttribute()
	{
		if ($this->getBinary())
			return 'BINARY';
		if ($this->getUnsigned() && $this->getZerofill())
			return 'UNSIGNED ZEROFILL';
		if ($this->getUnsigned())
			return 'UNSIGNED';
		return '';
	}

	public function setEnum($val)
	{
		if (is_array($val))
		{
			$val = array_map('addslashes', $val);
			$this->enum = '(' . implode(", ", $val) . ')';
		} else
			$this->enum = $val;
	}

	public function getEnum()
	{
		return $this->enum;
	}

	public function setSet($val)
	{
		if (is_array($val))
		{
			$val = array_map('addslashes', $val);
			$this->set = '(' . implode(", ", $val) . ')';
		} else
			$this->set = $val;
	}

	public function getSet()
	{
		return $this->set;
	}

	/**
	 * Set row as primary or not
	 * @param $bool
	 */
	public function setPrimary($bool)
	{
		$this->primary = $bool;
	}

	public function getPrimary()
	{
		return $this->primary;
	}

	/**
	 * Set row as autoincrement or not
	 * @param $bool
	 */
	public function setAutoIncrement($bool)
	{
		$this->auto_increment = $bool;
	}

	public function getAutoIncrement()
	{
		return $this->auto_increment;
	}

	/**
	 * Set row as null or not null
	 * @param $bool
	 */
	public function setNull($bool)
	{
		$this->null = $bool;
	}

	public function getNull()
	{
		return $this->null;
	}

	/**
	 * Set references value for row
	 * @param $value
	 */
	public function setReferences($value)
	{
		$this->references = $value;
	}

	public function getReferences()
	{
		return $this->references;
	}

	/**
	 * Set comment value for row
	 * @param $value
	 */
	public function setComment($value)
	{
		$this->comment = $value;
	}

	/**
	 * Get comment value for row
	 * @return mixed
	 */
	public function getComment()
	{
		return $this->comment;
	}

	/**
	 * Set default value for row
	 * @param $value
	 */
	public function setDefault($value)
	{
		$this->default = $value;
	}

	/**
	 * Get default value for row
	 * @return mixed
	 */
	public function getDefault()
	{
		return $this->default;
	}

	/**
	 * Returns row name
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Concat default string
	 * @return int|string
	 */
	private function getDefaultString()
	{
		return is_numeric($this->default) ? $this->default : ('\'' . addslashes($this->default) . '\'');
	}

	/**
	 * Returns possibles values for enum and set rows
	 */
	public function getPossibleValues()
	{
		$values = explode(',', $this->getLength());
		$result = array();
		foreach ($values as $val)
			$result[] = stripslashes(substr($val, 1, -1));
		return $result;
	}

	/**
	 * Concat mysql row creation string
	 * @return string
	 */
	public function getMySQLString()
	{
		$query = $this->name . ' ' . $this->type;
		$query .= ($this->length !== '' && is_int($this->length) ? '(' . intval($this->length) . ')' : '');
		$query .= ($this->unsigned ? ' UNSIGNED' : '');
		$query .= ($this->zerofill ? ' ZEROFILL' : '');
		$query .= ($this->binary ? ' BINARY' : '');
		$query .= ($this->ascii ? ' ASCII' : '');
		$query .= ($this->unicode ? ' UNICODE' : '');
		$query .= ($this->enum != '' ? '(' . $this->enmu . ')' : '');
		$query .= ($this->set != '' ? '(' . $this->set . ')' : '');
		$query .= ($this->null !== '' ? ($this->null ? ' NULL' : ' NOT NULL') : '');
		$query .= ($this->default !== '' ? ' DEFAULT ' . $this->getDefaultString() : '');
		$query .= ($this->auto_increment ? ' AUTO_INCREMENT' : '');
		$query .= ($this->primary ? ' PRIMARY KEY' : '');
		$query .= ($this->comment !== '' ? ' COMMENT \'' . addslashes($this->comment()) . '\'' : '');
		$query .= ($this->references !== '' ? ' REFERENCES ' . $this->references : '');
		return $query;
	}

}