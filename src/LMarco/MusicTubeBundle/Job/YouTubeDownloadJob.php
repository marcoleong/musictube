<?php 
namespace LMarco\MusicTubeBundle\Job;

// use Xaav\QueueBundle\Queue\Job\JobInterface;
use Symfony\Component\Process\Process;


class YouTubeDownloadJob
{
	static $rns = "JAMTUBE";
	public $url;
	public $cwd;
	public $jobId;
	public $container;
	public $downloadProcess;
	public $convertProcess;
	public $fileFormat;
	static $youtubeDl = "/usr/local/bin/youtube-dl";
	static $ffmpeg = "/usr/local/bin/ffmpeg";

	public function __construct($container,$url, $cwd, $jobId)
	{
		$this->container = $container;
		$this->jobId = $jobId;
		$this->url = $url;
		$this->cwd = $cwd;
	}

	public function process()
	{
		$predis = $this->container->get('snc_redis.default');
		$inputName = $this->jobId.'.mp4';
		$outputName = $this->jobId.'.mp3';

		$env = array("PATH" => "/usr/local/bin/jamtube:/usr/bin");
		$this->downloadProcess = new Process(
			sprintf("%s --extract-audio --audio-format mp3 --audio-quality 320k '%s'", self::$youtubeDl, $this->url), 
			$this->cwd, //working directory
			$env, //env
			null,
			1000); //time out

		$jobId = $this->jobId;
		$this->downloadProcess->run(function ($type, $buffer) use (&$predis, $jobId) {
                if ('err' === $type) {
                    $predis->set($jobId,'ERR > '.$buffer);
                } else {
                	$matches = array();
                	preg_match('/([0-9\.]+%)/',$buffer, $matches);
                	if(count($matches) > 0){
                		$percent = floatval($matches[0]);
	                	$predis->set($jobId, $percent);
                	}
                }
            }
        );

	}

	public function convert()
	{
		$predis = $this->container->get('snc_redis.default');
		$process = new Process("");
	}


}