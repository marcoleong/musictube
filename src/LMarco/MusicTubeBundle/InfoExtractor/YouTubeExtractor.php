<?php

namespace LMarco\MusicTubeBundle\InfoExtractor;

use Symfony\Component\Process\Process;
class YouTubeExtractor
{
	public $youTubeUrl;

	public function __construct($url)
	{
		$this->youTubeUrl = $url;
	}

	public function getTitle()
	{
		$cmd = '/usr/local/bin/youtube-dl -e '.$this->youTubeUrl;
		$title = $this->runCommand($cmd);
        if($title){
        	return $title;
        }else{
        	return false;
        }
	}

	public function getVideoId()
	{
		$url = $this->youTubeUrl;
		parse_str( parse_url( $url, PHP_URL_QUERY ), $vars );
		return $vars['v'];  
	}

	public function getYouTubeUrl()
	{
		return $this->youTubeUrl;
	}

	private function runCommand($cmd)
	{
		$process = new Process($cmd);
		$process->run();
		if($process->isSuccessful()) {
            return $process->getOutput();
        }else{
        	return false;
        }
	}
}