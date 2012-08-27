<?php
/**
 * http://en.wikipedia.org/wiki/REST
 */
abstract class RestController
{
	protected $method = null;
	protected $uri = null;
	protected $resource = null;
	protected $id = null;
	protected $supportedResources = array();
	protected $output = array();
	protected $serializer = null;
	
	public function __construct($method, $uri, $serializer)
	{	
		$this->uri = $uri;
		$this->serializer = new JsonSerializer();

		$match = array();
		
		if(
			preg_match('|^/.*\.php/([^/]+)/?(\d*).*$|', $this->uri, $match)
			or
			preg_match('|^/([^/]+)/?(\d*).*$|', $this->uri, $match)
		)
		{
			$this->resource = trim($match[1]);
			$this->id = (int) $match[2];
		}
		else
		{
			throw new Exception('Not a RESTful request' . $this->uri);
		}
		
		$match = array();
		
		// Check wether method is given as "_method" parameter value:
		if(strtoupper($method) == 'POST' and preg_match('/.*?\W_method=(GET|POST|PUT|DELETE).*?/i', $this->uri, $match))
		{
			$this->method = $match[1];
		}
		else
		{
			$this->method = $method;
		}
		
		// Normalize method name:
		$this->method = strtoupper($this->method);
		
		$this->handleRequest();
	}
	
	protected function handleRequest()
	{	
		if(false === array_key_exists($this->resource, $this->supportedResources))
		{
			throw new Exception(sprintf("Resource: %s is not supported.", $this->resource));
		}
		
		$temp = file_get_contents("php://input");

		$data = $this->serializer->unserialize($temp);
		
		switch($this->method)
		{
			case "GET":
				
				if($this->id != null and is_int($this->id))
				{
					$this->processGetElement();
				}
				else
				{
					$this->processGetCollection();
				}
				
				break;

			case "POST":

				if($this->id != null and is_int($this->id))
				{
					$this->processPostElement($data);
				}
				else
				{
					$this->processPostCollection($data);
				}

				break;

			case "PUT":

				if($this->id != null and is_int($this->id))
				{
					$this->processPutElement($data);
				}
				else
				{
					$this->processPutCollection($data);
				}

				break;

			case "DELETE":
				if($this->id != null and is_int($this->id))
				{
					$this->processDeleteElement();
				}
				else
				{
					$this->processDeleteCollection();
				}

				break;

			default:
				throw new Exception('Not a REST request' . $this->method);
				break;
		}
	}
	
	protected function processGetElement()
	{
		printf(
			" * Retrieve a representation of the addressed member of the collection, expressed in an appropriate Internet media type.\n\n" .
			" * Resource: %s, id: %s\n", $this->resource, $this->id
		);
	}
	
	protected function processGetCollection()
	{
		printf(
			" * List the URIs and perhaps other details of the collection's members.\n\n" .
			" * Resource: %s\n\n", $this->resource
		);
	}
	
	protected function processPutElement($data)
	{
		printf(
			" * Replace the addressed member of the collection, or if it doesn't exist, create it.\n\n" .
			" * Resource: %s, id: %s\n\n", $this->resource, $this->id
		);
		
		printf(" * Data: \n\n%s", print_r($data, true));		
	}
	
	protected function processPutCollection($data)
	{
		printf(
			" * Replace the entire collection with another collection.\n\n" .
			" * Resource: %s\n\n", $this->resource
		);

		printf(" * Data: \n\n%s", print_r($data, true));
	}
	
	protected function processPostElement($data)
	{
		printf(
			" * Treat the addressed member as a collection in its own right and create a new entry in it.\n\n" .
			" * Resource: %s, id: %s\n\n", $this->resource, $this->id
		);
		
		printf(" * Data: \n\n%s", print_r($data, true));
	}

	protected function processPostCollection($data)
	{
		printf(
			" * Create a new entry in the collection. The new entry's URL is assigned automatically and is usually returned by the operation.\n\n" .
			" * Resource: %s\n\n", $this->resource
		);

		printf(" * Data: \n\n%s", print_r($data, true));
	}
	

	protected function processDeleteElement()
	{
		printf(
			" * Delete the addressed member of the collection.\n\n" .
			" * Resource: %s, id: %s\n\n", $this->resource, $this->id
		);
	}
	
	protected function processDeleteCollection()
	{
		printf(
			" * Delete the entire collection.\n\n" .
			" * Resource: %s\n\n", $this->resource
		);
	}
	
	public function getOutput()
	{
		return $this->output;
	}
	
	public function getSerializer()
	{	
		return $this->serializer;
	}
	
	public function setSerializer($serializer)
	{	
		$this->serializer = $serializer;
	}
}