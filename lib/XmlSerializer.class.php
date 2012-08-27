<?php

class XmlSerializer extends Serializer
{
	protected $mimeType = 'application/xml';
	
	public function serialize($data)
	{
		$output = '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
		$output .= "<ENTITIES>\n";

		foreach($data as $entity)
		{
			$output .= "\t<ENTITIY>\n";

			foreach($entity as $key => $value)
			{
				$output .= sprintf("\t\t<%s>%s</%s>\n", $key, $value, $key);
			}
			$output .= "\t</ENTITIY>\n";
		}

		$output .= "</ENTITIES>\n";
		
		return $output;
	}
}