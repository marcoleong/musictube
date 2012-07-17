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
 * @Route("/music")
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
            $entity->setTitle($extractor->getTitle());
            $entity->setVideoId($extractor->getVideoId());


            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('music_show', array('id' => $entity->getId())));
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

        $entity = $em->getRepository('LMarcoMusicTubeBundle:Music')->findOneById($id);

        $entity->setStatus(1);
        $em->flush($entity);

        $process = new Process("youtube-dl --extract-audio --audio-format mp3 --audio-quality 320k ".$entity->getYouTubeUrl(), '/private/tmp');
        $process->run();

        if($process->isSuccessful()){
            $entity->setStatus(2);
            $em->flush($entity);

            $tmpFs = new Filesystem(new LocalAdapter('/private/tmp/'));

            $musicFs = new Filesystem(new LocalAdapter($this->container->getParameter('kernel.root_dir').'/../web/music_files/'));

            if($tmpFs->has($entity->getVideoId().'.aac')){
                $file = $tmpFs->get($entity->getVideoId().'.aac');
                $musicFs->write($entity->getVideoId().'.aac',$file->getContent());
                $tmpFs->delete($entity->getVideoId().'.aac');
            }
        }        
       
        return new Response('',200);
    }

}
