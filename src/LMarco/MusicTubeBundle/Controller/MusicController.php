<?php

namespace LMarco\MusicTubeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use LMarco\MusicTubeBundle\Entity\Music;
use LMarco\MusicTubeBundle\Form\MusicType;
use Symfony\Component\Process\Process;

use Symfony\Component\HttpFoundation\Response;
use LMarco\MusicTubeBundle\Entity\MusicManager;

use LMarco\MusicTubeBundle\InfoExtractor\YouTubeExtractor;

use Gaufrette\Filesystem;
use Gaufrette\Adapter\Local as LocalAdapter;

use LMarco\MusicTubeBundle\Job\YouTubeDownloadJob;


/**
 * Music controller.
 *
 */
class MusicController extends Controller
{

    /**
     * Lists all Music entities.
     *
     * @Route("/", name="music")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('LMarcoMusicTubeBundle:Music')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Finds and displays a Music entity.
     *
     * @Route("/{id}/show", name="music_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('LMarcoMusicTubeBundle:Music')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Music entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to create a new Music entity.
     *
     * @Route("/new", name="music_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Music();
        $form   = $this->createForm(new MusicType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a new Music entity.
     *
     * @Route("/create", name="music_create")
     * @Method("post")
     * @Template("LMarcoMusicTubeBundle:Music:new.html.twig")
     */
    public function createAction()
    {
        $entity  = new Music();
        $request = $this->getRequest();
        $form    = $this->createForm(new MusicType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // set all the information.
            $extractor = new YouTubeExtractor($entity->getYouTubeUrl());

            // check if entity with same url exist;
            $existEntity = $em->getRepository('LMarcoMusicTubeBundle:Music')->findOneByVideoId($extractor->getVideoId());

            // if entity is exist,
            if($existEntity){
                // check if the file exist
                $mm = $this->container->get('musictube.music_orm_manager');

                $mm->updateLocalPath($existEntity);
                // //validate also file exist.
                if($mm->isDownloadable($existEntity)){
                    $response = new Response(json_encode(array(
                        "progress" => 'CONVERTED',
                        "videoId" => $existEntity->getVideoId()
                        )
                    ));
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;
                }else{
                    $existEntity->setStatus(0);
                    $em->flush($existEntity);
                }
                
            }
            $entity->setTitle($extractor->getTitle());
            $entity->setVideoId($extractor->getVideoId());

            $em->persist($entity);
            $em->flush();


            $vidData = array(
                'videoId' => $extractor->getVideoId()
                );
            return new Response(json_encode($vidData), 200);
            // return $this->redirect($this->generateUrl('music_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Music entity.
     *
     * @Route("/{id}/edit", name="music_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('LMarcoMusicTubeBundle:Music')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Music entity.');
        }

        $editForm = $this->createForm(new MusicType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Music entity.
     *
     * @Route("/{id}/update", name="music_update")
     * @Method("post")
     * @Template("LMarcoMusicTubeBundle:Music:edit.html.twig")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('LMarcoMusicTubeBundle:Music')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Music entity.');
        }

        $editForm   = $this->createForm(new MusicType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('music_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Music entity.
     *
     * @Route("/{id}/delete", name="music_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('LMarcoMusicTubeBundle:Music')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Music entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('music'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }

    /**
     * Start download process.
     *
     * @Route("/{id}/start_download", name="music_start_download", options={"expose"=true})
     * @Method("get")
     */
    public function startDownloadAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('LMarcoMusicTubeBundle:Music')->findOneByVideoId($id);

        $mm = $this->container->get('musictube.music_orm_manager');

        if($entity->getStatus() === 'CONVERTED' && $mm->isDownloadable($entity) ){
            $predis = $this->container->get("snc_redis.default");
            $predis->set($entity->getVideoId(), "CONVERTED");

            $mm->makeDownloadable($entity);          

            return new Response("CONVERTED", 200);
        }

        if($entity->getStatus() === 'NOT_CONVERTED')
        {
            $entity->setStatus(1);
            $em->flush($entity);

            $job = new YouTubeDownloadJob($this->container, $entity->getYouTubeUrl(),  '/private/tmp/', $entity->getVideoId());
            $job->process();

            if($job->downloadProcess->isSuccessful()){
                $entity->setStatus(2);
                $em->flush($entity);
                $mm->makeDownloadable($entity);          
            }
        }
        return new Response($entity->getStatus(),200);
    }

    /**
     * Get download link
     * @Route("/{videoId}/get_link", name="music_get_download_link", options={"expose" = true})
     * @Method("get")
     */
    public function getDownloadLinkAction($videoId)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('LMarcoMusicTubeBundle:Music')->findOneByVideoId($videoId);
        if($entity->getDownloadable()){
            $url = "/music_files/".$entity->getFilename();
            $response = new Response(json_encode(array('url' => $url)));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }
    /**
     * Check progress
     * 
     * @Route("{jobId}/progress/", name="music_get_progress", options={"expose"=true})
     * @Method("get")
     */
    public function getProgressAction($jobId)
    {
        $predis = $this->container->get("snc_redis.default");
        $progress = $predis->get($jobId);
        return new Response($progress, 200);
    }
}
