<?php

class JsonSerializer extends Serializer
{
	protected $mimeType = 'application/json';
	
	public function serialize($data)
	{
		return json_encode($data);
	}
}