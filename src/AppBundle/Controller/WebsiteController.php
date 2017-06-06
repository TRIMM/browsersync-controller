<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Website;
use AppBundle\Form\DeleteButtonType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * Website controller.
 *
 * @Route("website")
 */
class WebsiteController extends Controller
{
    /**
     * Lists all website entities.
     *
     * @Route("/", name="website_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $websites = $em->getRepository('AppBundle:Website')->findAll();

        return $this->render('website/index.html.twig', array(
            'websites' => $websites,
        ));
    }

    /**
     * Creates a new website entity.
     *
     * @Route("/new", name="website_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $website = new Website();
        $form = $this->createForm('AppBundle\Form\WebsiteType', $website);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if($website->getAvatar() instanceof UploadedFile) {

                /** @var UploadedFile $file */
                $file = $website->getAvatar();
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();

                $file->move(
                    $this->getParameter('avatar_directory'),
                    $fileName
                );

                $website->setAvatar($fileName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($website);
            $em->flush();

            return $this->redirectToRoute('website_index');
        }

        return $this->render('website/new.html.twig', array(
            'website' => $website,
            'form' => $form->createView(),
        ));
    }

        /**
     * Displays a form to edit an existing website entity.
     *
     * @Route("/{id}/edit", name="website_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Website $website)
    {
        $avatar = $website->getAvatar();
        $editForm = $this->createForm('AppBundle\Form\WebsiteType', $website);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            /** @var UploadedFile $file */
            if($website->getAvatar() instanceof UploadedFile) {
                $file = $website->getAvatar();
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();

                $file->move(
                    $this->getParameter('avatar_directory'),
                    $fileName
                );

                $website->setAvatar($fileName);
            } else {
                $website->setAvatar($avatar);
            }

            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('website_edit', array('id' => $website->getId()));
        }

        return $this->render('website/edit.html.twig', array(
            'website' => $website,
            'edit_form' => $editForm->createView()
        ));
    }

    /**
     * Deletes a website entity.
     *
     * @Route("/{id}/delete", name="website_delete")
     * @Method({"GET", "POST"})
     */
    public function deleteAction(Request $request, Website $website)
    {
        $form = $this->createFormBuilder()
            ->add('submit', DeleteButtonType::class, array(
                'label' => 'Delete',
                'attr' => array('class' => 'btn waves-effect waves-light red')
            ))
            ->setAction($this->generateUrl('website_delete', array('id' => $website->getId())))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($website);
            $em->flush();
            return $this->redirectToRoute('website_index');
        }

        return $this->render('website/delete.html.twig', array(
            'website' => $website,
            'delete_form' => $form->createView()
        ));

    }
}
