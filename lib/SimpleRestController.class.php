<?php
/**
 *
 */
class SimpleRestController extends RestController
{
	const DBHOST = 'localhost';
	const DBNAME = 'rest_area';
	const DBUSER = 'root';
	const DBPASS = '';

	/**
	 * if set to true deleting entire collection ($this->processDeleteCollection())
	 * is processed with TRUNCATE TABLE
	 * otherwise with DELETE FROM
	 * @var boolean
	 */
	private $truncate = false;
	
	private $db = null;

	public function __construct($method, $uri)
	{	
		$this->supportedResources = array(
			'addresses' => array(
				'table' => 'address',
				'id' => 'ADDRESSID',
			),
		);

		$this->db = mysql_connect(self::DBHOST, self::DBUSER, self::DBPASS);
		mysql_select_db(self::DBNAME);
		mysql_query('SET NAMES utf8');

		if (!$this->db) {
			throw new Exception('Database not available: ' . mysql_error());
		}

		parent::__construct($method, $uri, new Serializer());
	}
	
	protected function processPutElement($data)
	{
		$values = array();
		$fieldsNames = array();
				
		$fields = mysql_list_fields(
			self::DBNAME,
			$this->supportedResources[$this->resource]['table'],
			$this->db
		);
		
		$columns = mysql_num_fields($fields);

		for ($i = 0; $i < $columns; $i++) {
			$fieldNames[] = mysql_field_name($fields, $i);
		}
		
		$record = $data[0];
	
		foreach($record as $key => $val)
		{
			$temp = trim($val);
			$value = strlen($temp) > 0 ?  mysql_real_escape_string($temp) : null;
			$values[] = $temp;

			if(false === in_array($key, $fieldNames))
			{
				throw new Exception("Unknown field: $key in resource: " . $this->resource);
			}
		}
		
		$keys = array_keys($record);
		
		$updates = array();
		
		for($i = 0; $i < count($keys); $i++)
		{
			$updates[] = sprintf("`%s` = '%s'", $keys[$i], $values[$i]);
		}
		
		array_unshift($keys, $this->supportedResources[$this->resource]['id']);
		array_unshift($values, $this->id);
		
		$v = preg_replace("/,'',/", ',NULL,', implode("','", $values));
		$u = preg_replace("/= ''/", '= NULL,', implode(",", $updates));
		
		$query = sprintf("insert into `%s` (`%s`) VALUES ('%s') on duplicate key update %s",
			$this->supportedResources[$this->resource]['table'],
			implode('`,`', $keys),
			$v,
			$u
		);
		
		$result = mysql_query($query);
		
		if(mysql_error())
		{
			throw new Exception(mysql_error());
		}

		$this->output[] = mysql_insert_id();
		$this->output[] = mysql_insert_id();

		mysql_close($this->db);
	}
	
	protected function processPutCollection($data)
	{
		$this->processDeleteCollection();
		$this->output = array();
		$this->processPostCollection($data);
	}
	
	protected function processDeleteElement()
	{
		$query = sprintf("delete from `%s` where `%s`='%d'",
			$this->supportedResources[$this->resource]['table'],
			$this->supportedResources[$this->resource]['id'],
			$this->id
		);
		
		$result = mysql_query($query);
		
		$this->output[] = $result;
	}

	protected function processDeleteCollection()
	{
		$deleteMethod = $this->truncate === true ? 'truncate table' : 'delete from';
		
		$query = sprintf("%s `%s`",
			$deleteMethod,
			$this->supportedResources[$this->resource]['table']
		);

		$result = mysql_query($query);
		
		$this->output[] = $result;
	}
	
	protected function processGetElement()
	{
		$result = mysql_query(
			sprintf("select * from `%s` where `%s`='%d'",
				$this->supportedResources[$this->resource]['table'],
				$this->supportedResources[$this->resource]['id'],
				$this->id
			)
		);
		
		while ($row = mysql_fetch_assoc($result)) {
			$this->output[] = $row;
		}

		mysql_free_result($result);
		mysql_close($this->db);
	}
	
	protected function processGetCollection()
	{
		$result = mysql_query(
			sprintf("select * from `%s`",
				$this->supportedResources[$this->resource]['table']
			)
		);
		
		while ($row = mysql_fetch_assoc($result)) {
			$this->output[] = $row;
		}

		mysql_free_result($result);
		mysql_close($this->db);
	}

	protected function processPostElement($data)
	{
		$this->processPostCollection($data);
	}
	
	protected function processPostCollection($data)
	{
		if($data == null)
		{
			throw new Exception('No proper data');
		}
		
		$values = array();
		$fieldsNames = array();
		
		$fields = mysql_list_fields(
			self::DBNAME,
			$this->supportedResources[$this->resource]['table'],
			$this->db
		);
		
		$columns = mysql_num_fields($fields);

		for ($i = 0; $i < $columns; $i++) {
			$fieldNames[] = mysql_field_name($fields, $i);
		}
		
		$vc = 0;
		
		foreach($data as $record)
		{			
			foreach($record as $key => $val)
			{
				$temp = trim($val);
				$value = strlen($temp) > 0 ?  mysql_real_escape_string($temp) : null;
				$values[$vc][] = $temp;

				if(false === in_array($key, $fieldNames))
				{
					throw new Exception("Unknown field: $key in resource: " . $this->resource);
				}
			}

			$vc++;
		}

		$vc = 0;
		
		foreach($data as $record)
		{
			$keys = array_keys($record);
			
			$v = preg_replace("/,'',/", ',NULL,', implode("','", $values[$vc++]));
			
			$query = sprintf("insert into `%s` (`%s`) VALUES ('%s')",
				$this->supportedResources[$this->resource]['table'],
				implode('`,`', $keys),
				$v
			);
			
			$result = mysql_query($query);
			
			if(mysql_error())
			{
				throw new Exception(mysql_error());
			}
			
			$this->output[] = mysql_insert_id();
		}

		mysql_close($this->db);
	}

}