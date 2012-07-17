<?php 
namespace LMarco\MusicTubeBundle\Job;

// use Xaav\QueueBundle\Queue\Job\JobInterface;
use Symfony\Component\Process\Process;


class YouTubeDownloadJob
{
	public $url;
	public $cwd;
	
	public function __construct($url, $cwd)
	{
		$this->url = $url;
		$this->cwd = $cwd;
	}

	public function process()
	{
		$process = new Process("youtube-dl --extract-audio ".$this->url, $this->cwd);
		$process->run();
	}
}