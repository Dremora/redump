<?php

class Paginator
{
	private $totalPages;
	private $currentPage;
	private $baseLink;
	
	function __construct($currentPage, $totalPages, $baseLink = '')
	{
		$this->setCurrentPage($currentPage);
		$this->setTotalPages($totalPages);
		$this->setBaseLink($baseLink);
	}
	
	public function setCurrentPage($currentPage)
	{
		if (!$currentPage) $currentPage = 1;
		$this->currentPage = $currentPage;
	}
	
	public function setTotalPages($totalPages)
	{
		$this->totalPages = $totalPages;
	}
	
	public function setBaseLink($baseLink)
	{
		$this->baseLink = $baseLink;
	}
	
	private function getLink($pageNumber)
	{
		if ($pageNumber != $this->currentPage)
		{
			return '<a href="'.$this->baseLink.($pageNumber != 1 ? '?page='.$pageNumber : '').'">'.$pageNumber.'</a>';
		}
		else
		{
			return '<strong>'.$pageNumber.'</strong>';
		}
	}
	
	public function generate()
	{
		if ($this->totalPages < 2)
		{
			return;
		}
		
		$output = '<ul>';
		
		$pages = array(
			1, 2, 3, 4, 5,
			$this->totalPages-4, $this->totalPages-3, $this->totalPages-2, $this->totalPages-1, $this->totalPages
		);
		
		if (is_int($this->currentPage))
		{
			$pages = array_merge($pages,
				array($this->currentPage-2, $this->currentPage-1, $this->currentPage, $this->currentPage+1, $this->currentPage+2)
			);
		}
		
		$pages = array_unique($pages);
		sort($pages);
		
		$previous = 0;
		foreach ($pages as $page)
		{
			if ($page < 1 || $page > $this->totalPages) continue;
			if ($page - $previous == 2)
			{
				$output .= '<li>'.$this->getLink($page - 1).'</li>';
			}
			else if ($page - $previous > 2)
			{
				$output .= '<li>…</li>';
			}	
			$output .= '<li>'.$this->getLink($page).'</li>';
			$previous = $page;
		}
		
		$output .= '<li>'.$this->getLink('all').'</li>';
		$output .= '</ul>';
		return $output;
	}
}
