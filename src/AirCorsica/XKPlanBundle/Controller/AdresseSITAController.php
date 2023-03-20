<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\AdresseSITA;
use AirCorsica\XKPlanBundle\Entity\GroupeSITA;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Adressesitum controller.
 *
 */
class AdresseSITAController extends Controller
{
    /**
     * Lists all adresseSITum entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $adresseSITAs = $em->getRepository('AirCorsicaXKPlanBundle:AdresseSITA')->findAll();

        return $this->render('AirCorsicaXKPlanBundle:Adressesita:index.html.twig', array(
            'adresseSITAs' => $adresseSITAs,
        ));
    }

    /**
     * Creates a new adresseSITum entity.
     * @Security("has_role('ROLE_ADMIN')")
     * @ParamConverter("groupe_id", class="AirCorsicaXKPlanBundle:GroupeSITA")
     */
    public function newAction(Request $request, GroupeSITA $groupe_id)
    {
        $adresseSITum = new AdresseSITA();

        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\AdresseSITAType', $adresseSITum, array(
            'attr' => array(
                   'idGroupe' => $groupe_id->getId(),
                )));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($adresseSITum);
            $em->flush($adresseSITum);

            $request->getSession()->getFlashBag()->add('success', 'Adresse SITA créée avec succès.');

            return $this->redirectToRoute('groupesita_index');
        }

        return $this->render('AirCorsicaXKPlanBundle:Adressesita:new.html.twig', array(
            'adresseSITum' => $adresseSITum,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a adresseSITum entity.
     *
     */
    public function showAction(AdresseSITA $adresseSITum)
    {
        $deleteForm = $this->createDeleteForm($adresseSITum);

        return $this->render('AirCorsicaXKPlanBundle:Adressesita:show.html.twig', array(
            'adresseSITum' => $adresseSITum,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing adresseSITum entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, AdresseSITA $adresseSITum)
    {
        $deleteForm = $this->createDeleteForm($adresseSITum);
        $editForm = $this->createForm('AirCorsica\XKPlanBundle\Form\AdresseSITAType', $adresseSITum);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $request->getSession()->getFlashBag()->add('success', 'Adresse SITA modifiée avec succès.');

            return $this->redirectToRoute('groupesita_index');
        }

        return $this->render('AirCorsicaXKPlanBundle:Adressesita:edit.html.twig', array(
            'adresseSITum' => $adresseSITum,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a adresseSITum entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, AdresseSITA $adresseSITum)
    {

        $em = $this->getDoctrine()->getManager();

        $AdresseSITA = $em->getRepository('AirCorsicaXKPlanBundle:AdresseSITA')->find($adresseSITum);

        if (!$AdresseSITA) {
            throw $this->createNotFoundException('Cette Adresse SITA n\'existe pas');
        }
        $em->remove($AdresseSITA);
        $em->flush($AdresseSITA);

        $request->getSession()->getFlashBag()->add('success', 'Adresse SITA supprimée avec succès.');

        return $this->redirectToRoute('groupesita_index');
    }

    /**
     * Creates a form to delete a adresseSITum entity.
     *
     * @param AdresseSITA $adresseSITum The adresseSITum entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(AdresseSITA $adresseSITum)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('adressesita_delete', array('id' => $adresseSITum->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
