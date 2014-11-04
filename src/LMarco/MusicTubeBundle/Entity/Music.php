<?php

namespace LMarco\MusicTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * LMarco\MusicTubeBundle\Entity\Music
 *
 * @ORM\Table()
 * @ORM\Entity
 * @UniqueEntity("videoId")
 */
class Music
{

    public static $statusCode = array( 0 => 'NOT_CONVERTED',
                                       1 => 'CONVERTING',
                                       2 => 'CONVERTED');
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;


    /**
     * @var boolean $converted
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status = 0;

    /**
     * @var string $youTubeUrl
     *
     * @ORM\Column(name="youTubeUrl", type="string", length=255)
     */
    private $youTubeUrl;

    /**
     * @var string $localPath
     *
     * @ORM\Column(name="localPath", type="string", length=255, nullable=true)
     */
    private $localPath;

    /**
     * @var string $videoId
     *
     * @ORM\Column(name="videoId", type="string", length=255, unique=true)
     */
    private $videoId;

    /**
     * @var string $downloadable
     *
     * @ORM\Column(name="downloadable", type="boolean")
     */
    private $downloadable = false;

    /**
     * @var string $downloadPath
     *
     * @ORM\Column(name="downloadPath", type="string", length=255, nullable=true)
     */
    private $downloadPath;

    /**
     * @var string $format
     *
     * @ORM\Column(name="format", type="string", length=10, nullable=true)
     */
    private $format = 'mp3';


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Music
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set converted
     *
     * @param boolean $converted
     * @return Music
     */
    public function setConverted($converted)
    {
        $this->converted = $converted;
        return $this;
    }

    /**
     * Get converted
     *
     * @return boolean 
     */
    public function getConverted()
    {
        return $this->converted;
    }

    /**
     * Set localPath
     *
     * @param string $localPath
     * @return Music
     */
    public function setLocalPath($localPath)
    {
        $this->localPath = $localPath;
        return $this;
    }

    /**
     * Get localPath
     *
     * @return string 
     */
    public function getLocalPath()
    {
        return $this->localPath;
    }

    /**
     * Set youTubeUrl
     *
     * @param string $youTubeUrl
     * @return Music
     */
    public function setYouTubeUrl($youTubeUrl)
    {
        $this->youTubeUrl = $youTubeUrl;
        return $this;
    }

    /**
     * Get youTubeUrl
     *
     * @return string 
     */
    public function getYouTubeUrl()
    {
        return $this->youTubeUrl;
    }

    /**
     * Set videoId
     *
     * @param string $videoId
     * @return Music
     */
    public function setVideoId($videoId)
    {
        $this->videoId = $videoId;
        return $this;
    }

    /**
     * Get videoId
     *
     * @return string 
     */
    public function getVideoId()
    {
        return $this->videoId;
    }

    /**
     * Set status
     *
     * @param int $status
     * @return Music
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return int 
     */
    public function getStatus()
    {
        $statusCode = self::$statusCode;
        return $statusCode[$this->status];
    }

    /**
     * Set downloadable
     *
     * @param boolean $downloadable
     * @return Music
     */
    public function setDownloadable($downloadable)
    {
        $this->downloadable = $downloadable;
        return $this;
    }

    /**
     * Get downloadable
     *
     * @return boolean 
     */
    public function getDownloadable()
    {
        return $this->downloadable;
    }

    /**
     * Set downloadPath
     *
     * @param string $downloadPath
     * @return Music
     */
    public function setDownloadPath($downloadPath)
    {
        $this->downloadPath = $downloadPath;
        return $this;
    }

    /**
     * Get downloadPath
     *
     * @return string 
     */
    public function getDownloadPath()
    {
        return $this->downloadPath;
    }

    /**
     * Set format
     *
     * @param string $format
     * @return Music
     */
    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * Get format
     *
     * @return string 
     */
    public function getFormat()
    {
        return $this->format;
    }

    public function getFilename()
    {
        return $this->videoId.'.'.$this->format;
    }

    public function getVideoFilename()
    {
        return $this->videoId.'.mp4';
    }
}