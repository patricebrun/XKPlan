<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\PeriodeImmobilisation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Periodeimmobilisation controller.
 *
 */
class PeriodeImmobilisationController extends Controller
{
    /**
     * Lists all periodeImmobilisation entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $periodeImmobilisations = $em->getRepository('AirCorsicaXKPlanBundle:PeriodeImmobilisation')->findAll();

        return $this->render('periodeimmobilisation/index.html.twig', array(
            'periodeImmobilisations' => $periodeImmobilisations,
        ));
    }

    /**
     * Creates a new periodeImmobilisation entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $periodeImmobilisation = new Periodeimmobilisation();
        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\PeriodeImmobilisationType', $periodeImmobilisation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $periodeImmobilisation->setTemplate($em->find('AirCorsicaXKPlanBundle:Template',$this->get('session')->get('template')));
            $em->persist($periodeImmobilisation);
            $em->flush($periodeImmobilisation);

            return $this->redirectToRoute('periodeimmobilisation_show', array('id' => $periodeImmobilisation->getId()));
        }

        return $this->render('periodeimmobilisation/new.html.twig', array(
            'periodeImmobilisation' => $periodeImmobilisation,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a periodeImmobilisation entity.
     *
     */
    public function showAction(PeriodeImmobilisation $periodeImmobilisation)
    {
        $deleteForm = $this->createDeleteForm($periodeImmobilisation);

        return $this->render('periodeimmobilisation/show.html.twig', array(
            'periodeImmobilisation' => $periodeImmobilisation,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing periodeImmobilisation entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, PeriodeImmobilisation $periodeImmobilisation)
    {
        $deleteForm = $this->createDeleteForm($periodeImmobilisation);
        $editForm = $this->createForm('AirCorsica\XKPlanBundle\Form\PeriodeImmobilisationType', $periodeImmobilisation);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('periodeimmobilisation_edit', array('id' => $periodeImmobilisation->getId()));
        }

        return $this->render('periodeimmobilisation/edit.html.twig', array(
            'periodeImmobilisation' => $periodeImmobilisation,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a periodeImmobilisation entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, PeriodeImmobilisation $periodeImmobilisation)
    {
        $form = $this->createDeleteForm($periodeImmobilisation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($periodeImmobilisation);
            $em->flush($periodeImmobilisation);
        }

        return $this->redirectToRoute('periodeimmobilisation_index');
    }

    /**
     * Creates a form to delete a periodeImmobilisation entity.
     *
     * @param PeriodeImmobilisation $periodeImmobilisation The periodeImmobilisation entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(PeriodeImmobilisation $periodeImmobilisation)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('periodeimmobilisation_delete', array('id' => $periodeImmobilisation->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
