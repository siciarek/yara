<?php

class Serializer
{
	protected $mimeType = 'text/plain';
	
	public function getMimeType()
	{
		return $this->mimeType;
	}
	
	public function serialize($data)
	{
		return serialize($data);
	}

	public function unserialize($string)
	{
		return json_decode($string, true);
	}
}