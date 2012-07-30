<?php 
namespace LMarco\MusicTubeBundle\Entity;

use Processing\Connection\RedisConnection;
use Processing\Processor;

use Gaufrette\Filesystem;
use Gaufrette\Adapter\Local as LocalAdapter;


/**
 * - check if the file exist
 * - check is file downloaded
 */
class MusicManager
{
	const TEMP_DIR = '/private/tmp';

	public $container;

	public $em;
	public function __construct($container = null)
	{
		$this->container = $container;
		$this->em =  $this->container->get('doctrine')->getManager();
	}

	public function isDownloadable(Music $entity)
	{
		// true if file exist
		if($this->getWebFileSystem()->has($entity->getFilename())) {
			return true;
		}else{
			if($this->isMusicFileExistInTmp($entity, $entity->getFilename())) {
				$this->makeDownloadable($entity);
				return true;
			}else{
				return false;
			}
		}
	}


	public function getWebFileSystem()
	{
		$webPath = $this->container->getParameter('kernel.root_dir').'/../web/music_files/';
		return $this->getLocalFileSystem($webPath);
	}
	/**
	 * check if it exist in directory
	 */
	public function isMusicFileExistInTmp(Music $entity, $filename)
	{
		return $this->isInDirectory($entity, self::TEMP_DIR);
	}

	/**
	 * Copy to download directory
	 * @return true if success;
	 */
	public function makeDownloadable(Music $entity)
	{
		$webPath = $this->container->getParameter('kernel.root_dir').'/../web/music_files/';

		if($this->isInDirectory($entity, $webPath)){
			$this->updateLocalPath($entity);
			$entity->setDownloadable(true);
			$this->em->flush($entity);
			return;
		}

		if($this->isMusicFileExistInTmp($entity, $entity->getFilename())){
			$this->moveTo($entity, $this->container->getParameter('kernel.root_dir').'/../web/music_files/');
			$entity->setDownloadable(true);
			$this->em->flush($entity);
		}
	}

	/**
	 * Move a music file from to specific directory
	 */
	public function moveTo(Music $entity, $directory)
	{
		$this->updateLocalPath($entity);
		$content = file_get_contents($entity->getLocalPath());
		$toFs = $this->getLocalFileSystem($directory);
		$toFs->write($entity->getFilename(), $content);
	}


	public function updateLocalPath(Music $entity)
	{
		if($this->isMusicFileExistInTmp($entity, '.'.$entity->getFormat())){
			$entity->setLocalPath('/private/tmp/'.$entity->getFilename());
		}
		$webPath = $this->container->getParameter('kernel.root_dir').'/../web/music_files/';
		if($this->isInDirectory($entity,$webPath)){
			$entity->setLocalPath($webPath.$entity->getFilename());
		}
		$this->em->flush($entity);
	}

	private function getLocalFileSystem($directory)
	{
		return new FileSystem(new LocalAdapter($directory));
	}

	private function isInDirectory(Music $entity, $directoryPath)
	{
		$fs = $this->getLocalFileSystem($directoryPath);
		return $fs->has($entity->getVideoId().'.'.$entity->getFormat());
	}

	private function getFileName(Music $entity)
	{
		return $entity->getFilename();
	}
}