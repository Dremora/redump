<?php

if (!defined('PSXDB')) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
	die();
}

class Feed {
	private $contents;
	
	function  __construct($feed) {
		if (!isset($feed['title']) || !isset($feed['link']) || !isset($feed['description']))
			self::destruct();
		$this->contents .= '<?xml version="1.0" encoding="utf-8"?>'."\n";
		$this->contents .= '<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">'."\n";
		$this->contents .= '	<channel>'."\n";
		$this->contents .= '		<title><![CDATA['.$feed['title'].']]></title>'."\n";
		$this->contents .= '		<link>'.$feed['link'].'</link>'."\n";
		$this->contents .= '		<description><![CDATA['.$feed['description'].']]></description>'."\n";
		if ($feed['updated'] != 0)
			$this->contents .= '		<lastBuildDate>'.gmdate('D, d M Y H:i:s T', $feed['updated']).'</lastBuildDate>'."\n";
		$this->contents .= '		<generator>Dremora\'s RSS Feed generator v1.0</generator>'."\n";
	}
	
	function addItem($item) {
		if (!isset($item['title']) && !isset($item['description']))
			return;
		$this->contents .= '		<item>'."\n";
		if (isset($item['author']))
			$this->contents .= '			<dc:creator><![CDATA['.$item['author'].']]></dc:creator>'."\n";
		if (isset($item['category']))
			$this->contents .= '			<category><![CDATA['.$item['category'].']]></category>'."\n";
		if (isset($item['description']))
			$this->contents .= '			<description><![CDATA['.$item['description'].']]></description>'."\n";
		if (isset($item['guid']))
			$this->contents .= '			<guid isPermaLink="false"><![CDATA['.$item['guid'].']]></guid>'."\n";
		if (isset($item['link']))
			$this->contents .= '			<link>'.$item['link'].'</link>'."\n";
		if (isset($item['added']))
			$this->contents .= '			<pubDate>'.gmdate('D, d M Y H:i:s T', $item['added']).'</pubDate>'."\n";
		if (isset($item['title']))
			$this->contents .= '			<title><![CDATA['.$item['title'].']]></title>'."\n";
		$this->contents .= '		</item>'."\n";
	}
	
	function display() {
		$this->contents .= '	</channel>'."\n";
		$this->contents .= '</rss>'."\n";
		header('Content-type: application/xml');
		echo $this->contents;
	}
}

?>