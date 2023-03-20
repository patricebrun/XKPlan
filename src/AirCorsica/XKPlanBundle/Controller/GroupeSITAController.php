<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\AdresseSITA;
use AirCorsica\XKPlanBundle\Entity\GroupeSITA;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Groupesitum controller.
 *
 */
class GroupeSITAController extends Controller
{
    /**
     * Lists all groupeSITum entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $groupeSITAs = $em->getRepository('AirCorsicaXKPlanBundle:GroupeSITA')->findBy(array(),array('nom' => 'ASC'));

        return $this->render('AirCorsicaXKPlanBundle:Groupesita:index.html.twig', array(
            'groupeSITAs' => $groupeSITAs,
        ));
    }

    /**
     * Creates a new groupeSITum entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $groupeSITum = new GroupeSITA();
        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\GroupeSITAType', $groupeSITum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($groupeSITum);
            $em->flush($groupeSITum);

            $request->getSession()->getFlashBag()->add('success', 'Groupe SITA créé avec succès.');

            return $this->redirectToRoute("groupesita_index");
        }

        return $this->render('AirCorsicaXKPlanBundle:Groupesita:new.html.twig', array(
            'groupeSITum' => $groupeSITum,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a groupeSITum entity.
     *
     */
    public function showAction(GroupeSITA $groupeSITum)
    {
        $deleteForm = $this->createDeleteForm($groupeSITum);

        return $this->render('AirCorsicaXKPlanBundle:Groupesita:show.html.twig', array(
            'groupeSITum' => $groupeSITum,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing groupeSITum entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, GroupeSITA $groupeSITum)
    {
        $deleteForm = $this->createDeleteForm($groupeSITum);
        $editForm = $this->createForm('AirCorsica\XKPlanBundle\Form\GroupeSITAType', $groupeSITum);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $request->getSession()->getFlashBag()->add('success', 'Groupe SITA modifié avec succès.');

            return $this->redirectToRoute("groupesita_index");
        }

        return $this->render('AirCorsicaXKPlanBundle:Groupesita:edit.html.twig', array(
            'groupeSITum' => $groupeSITum,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a groupeSITum entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, GroupeSITA $groupeSITum)
    {
        $em = $this->getDoctrine()->getManager();

        $GroupeSITA = $em->getRepository('AirCorsicaXKPlanBundle:GroupeSITA')->find($groupeSITum);

        if (!$GroupeSITA) {
            throw $this->createNotFoundException('Ce Groupe SITA n\'existe pas');
        }
        $em->remove($GroupeSITA);
        $em->flush($GroupeSITA);

        $request->getSession()->getFlashBag()->add('success', 'Groupe SITA supprimé avec succès.');

        return $this->redirectToRoute('groupesita_index');
    }

    /**
     * Creates a form to delete a groupeSITum entity.
     *
     * @param GroupeSITA $groupeSITum The groupeSITum entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(GroupeSITA $groupeSITum)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('groupesita_delete', array('id' => $groupeSITum->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * Export a Groupe SITA entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function groupesitacsvAction(){

        $em = $this->getDoctrine()->getManager();

        $groupeSITAs = $em->getRepository('AirCorsicaXKPlanBundle:GroupeSITA')->findBy(array(),array('nom' => 'ASC'));

        $response = new StreamedResponse();
        $response->setCallback(function() use($groupeSITAs){

            $handle = fopen('php://output', 'w+');

            //Champs
            /** @var GroupeSITA $groupeSITA */
            foreach ($groupeSITAs as $groupeSITA)
            {
                /** @var AdresseSITA $adresse */
                foreach ($groupeSITA->getAdresses() as $adresse){
                    fputcsv($handle,array($groupeSITA->getNom(),$adresse->getLibelle()
                    ),';');
                }
            }
            fclose($handle);
        }
        );
        $response->setStatusCode(200);
        $now = new \DateTime();
        $response->headers->set('Content-Type', 'text/csv; charset=iso-8859-1');
        $response->headers->set('Content-Disposition','attachment; filename="export_groupesSITA_'.$now->format('dmY').'.csv"');

        return $response;
    }
}
