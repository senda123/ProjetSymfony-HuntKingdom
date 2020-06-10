<?php

namespace ReclamationBundle\Controller;

use ReclamationBundle\Entity\Reclamation;
use ReclamationBundle\Form\ReclamationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class ReclamationController extends Controller
{
    public function AjoutReclamationAction(Request $request)
    {
        $userID=$this->getUser()->getId();
        $userManager = $this->get('fos_user.user_manager');
        $reclamation=new Reclamation();
        $form=$this->createForm(ReclamationType::class,$reclamation);
        $form->handleRequest($request);
        if($form->isSubmitted())
        {
            $reclamation->setEtatReclamation(0);
            $reclamation->setDateReclamation(new \DateTime('now'));
            $user=$userManager->findUserBy(array('id'=>$userID));
            $reclamation->setIdUser($user);
            $em=$this->getDoctrine()->getManager();
            $em->persist($reclamation);
            $em->flush();
            return $this->redirectToRoute('AjouterReclamation');
        }
        return $this->render('@Reclamation/Reclamation/AjoutReclamation.html.twig',array('form'=>$form->createView()));
    }

    public function AfficherReclamationsFrontAction()
    {
        $em=$this->getDoctrine()->getManager();
        $userID = $this->getUser()->getID();
        $reclamations=$em->getRepository("ReclamationBundle:Reclamation")->RechercheReclamationUser($userID);
        return $this->render('@Reclamation/Reclamation/AfficherReclamation.html.twig',array('reclamation'=>$reclamations));
    }

    public function SupprimerReclamationFrontAction(Request $request)
    {
        $id=$request->get('id');
        $user=$this->getUser();
        $username=$this->getUser()->getUsername();
        $em=$this->getDoctrine()->getManager();
        $reclamation=$em->getRepository("ReclamationBundle:Reclamation")->find($id);
        $em->remove($reclamation);
        $em->flush();
        return $this->redirectToRoute('AfficherReclamation');
    }

    public function AfficherReclamationBackAction()
    {
        $doctrine=$this->getDoctrine();
        $repository=$doctrine->getRepository('ReclamationBundle:Reclamation');
        $reclamations=$repository->findAll();
        return $this->render('@Reclamation/Reclamation/AfficherReclamationBack.html.twig',array('reclamations'=>$reclamations));

    }

    public function SupprimerReclamationBackAction($id)
    {
        $em=$this->getDoctrine()->getManager();
        $reclamations=$em->getRepository("ReclamationBundle:Reclamation")->find($id);
        $em->remove($reclamations);
        $em->flush();
        return $this->redirectToRoute('AfficherReclamationBack');
    }

    public function TraiterReclamationAction(Request $request)
    {
        $id=$request->get('id');
        $em = $this->getDoctrine()->getManager();
        $reclamation = $em->getRepository('ReclamationBundle:Reclamation')->find($id);
        $reclamation->setEtatReclamation(1);
        $em->persist($reclamation);
        $em->flush();

        return $this->redirectToRoute('AfficherReclamationBack') ;
    }


}
