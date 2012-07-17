<?php

namespace LMarco\MusicTubeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Process\Process;

class MusicTubeController extends Controller
{
    /**
     * @Route("/", name="musictube_musictube_index")
     * @Template()
     */
    public function indexAction()
    {

    	// $process = new Process('youtube-dl http://www.youtube.com/watch?v=5FlQSQuv_mg --extract-audio');
    	// $process->setWorkingDirectory('/tmp');
    	// $process->run(function ($type, $buffer) {
					//     if ('err' === $type) {
					//         echo 'ERR > '.$buffer;
					//     } else {
					//         echo 'OUT > '.$buffer;
					//     }
					// });

		return $this->redirect($this->generateUrl('music'));		
    }

}
