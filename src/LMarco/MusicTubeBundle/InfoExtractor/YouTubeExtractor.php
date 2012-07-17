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
		$cmd = 'youtube-dl -e '.$this->youTubeUrl;
        return $this->runCommand($cmd);
	}

	public function getVideoId()
	{
		$url = $this->youTubeUrl;
		parse_str( parse_url( $url, PHP_URL_QUERY ), $my_array_of_vars );
		return $my_array_of_vars['v'];  
	}

	private function runCommand($cmd)
	{
		$process = new Process($cmd);
		$process->run();
		if($process->isSuccessful()) {
            return $process->getOutput();
        }
	}
}