<?php

class HtmlSerializer extends Serializer
{
	protected $mimeType = 'text/html';
	
	public function serialize($data)
	{
		return json_encode($data);
	}
}