<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Contact;
use AppBundle\Form\ContactType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/contacts")
 */

class ContactController extends Controller
{
    /**
     * @Route("/")
     */
    public function listAction()
    {
        $repo = $this->getDoctrine()->getRepository(Contact::class);
        $contactsList = $repo->findAll();

        return $this->render('AppBundle:Contact:list.html.twig', [
            'contacts' => $contactsList,
        ]);
    }

    /**
     * @Route("/add")
     */
    public function addAction(Request $request)
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contact = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($contact);
            $em->flush();

            $this->addFlash(
                'success',
                "Contact {$contact->getFirstName()} {$contact->getLastName()} has been created successfully"
            );

            return $this->redirectToRoute('app_contact_list');
        }

        return $this->render('AppBundle:Contact:add.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", requirements={"id": "[1-9][0-9]*"})
     */
    public function showAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Contact');
        $contact = $repo->find($id);

        return $this->render('AppBundle:Contact:show.html.twig', [
            'contact' => $contact
        ]);
    }

    /**
     * @Route("/{id}/update")
     */
    public function updateAction($id, Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Contact');
        $contact = $repo->find($id);

        $form = $this->createForm(ContactType::class);
        $form->setData($contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contact = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($contact);
            $em->flush();

            $this->addFlash(
                'success',
                "Contact {$contact->getFirstName()} {$contact->getLastName()} has been modified successfully"
            );

            return $this->redirectToRoute('app_contact_list');
        }

        return $this->render('AppBundle:Contact:update.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete")
     */
    public function deleteAction($id, Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Contact');
        $contact = $repo->find($id);

        if ($request->get('confirm') === 'yes') {
            $em = $this->getDoctrine()->getManager();
            $em->remove($contact);
            $em->flush();

            $this->addFlash(
                'success',
                "Contact {$contact->getFirstName()} {$contact->getLastName()} has been deleted successfully"
            );
        }

        if ($request->isMethod('POST')) {
            return $this->redirectToRoute('app_contact_list');
        }

        return $this->render('AppBundle:Contact:delete.html.twig', [
            'contact' => $contact
        ]);
    }

}
