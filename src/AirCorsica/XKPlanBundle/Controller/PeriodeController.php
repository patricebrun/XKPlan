<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\Periode;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Periode controller.
 *
 */
class PeriodeController extends Controller
{
    /**
     * Lists all periode entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $periodes = $em->getRepository('AirCorsicaXKPlanBundle:Periode')->findAll();

        return $this->render('periode/index.html.twig', array(
            'periodes' => $periodes,
        ));
    }

    /**
     * Creates a new periode entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $periode = new Periode();
        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\PeriodeType', $periode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($periode);
            $em->flush($periode);

            return $this->redirectToRoute('periode_show', array('id' => $periode->getId()));
        }

        return $this->render('periode/new.html.twig', array(
            'periode' => $periode,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a periode entity.
     *
     */
    public function showAction(Periode $periode)
    {
        $deleteForm = $this->createDeleteForm($periode);

        return $this->render('periode/show.html.twig', array(
            'periode' => $periode,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing periode entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, Periode $periode)
    {
        $deleteForm = $this->createDeleteForm($periode);
        $editForm = $this->createForm('AirCorsica\XKPlanBundle\Form\PeriodeType', $periode);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('periode_edit', array('id' => $periode->getId()));
        }

        return $this->render('periode/edit.html.twig', array(
            'periode' => $periode,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a periode entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, Periode $periode)
    {
        $form = $this->createDeleteForm($periode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($periode);
            $em->flush($periode);
        }

        return $this->redirectToRoute('periode_index');
    }

    /**
     * Creates a form to delete a periode entity.
     *
     * @param Periode $periode The periode entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Periode $periode)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('periode_delete', array('id' => $periode->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
