<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\PeriodeDeVol;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Periodedevol controller.
 *
 */
class PeriodeDeVolController extends Controller
{
    /**
     * Lists all periodeDeVol entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $periodeDeVols = $em->getRepository('AirCorsicaXKPlanBundle:PeriodeDeVol')->findAll();

        return $this->render('@AirCorsicaXKPlan/Periodedevol/index.html.twig', array(
            'periodeDeVols' => $periodeDeVols,
        ));
    }

    /**
     * Creates a new periodeDeVol entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $periodeDeVol = new Periodedevol();
        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\PeriodeDeVolType', $periodeDeVol);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($periodeDeVol);
            $em->flush($periodeDeVol);

            return $this->redirectToRoute('periodedevol_show', array('id' => $periodeDeVol->getId()));
        }

        return $this->render('@AirCorsicaXKPlan/Periodedevol/new.html.twig', array(
            'periodeDeVol' => $periodeDeVol,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a periodeDeVol entity.
     *
     */
    public function showAction(PeriodeDeVol $periodeDeVol)
    {
        $deleteForm = $this->createDeleteForm($periodeDeVol);

        return $this->render('@AirCorsicaXKPlan/Periodedevol/show.html.twig', array(
            'periodeDeVol' => $periodeDeVol,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing periodeDeVol entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, PeriodeDeVol $periodeDeVol)
    {
        $deleteForm = $this->createDeleteForm($periodeDeVol);
        $editForm = $this->createForm('AirCorsica\XKPlanBundle\Form\PeriodeDeVolType', $periodeDeVol);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('periodedevol_edit', array('id' => $periodeDeVol->getId()));
        }

        return $this->render('@AirCorsicaXKPlan/Periodedevol/edit.html.twig', array(
            'periodeDeVol' => $periodeDeVol,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a periodeDeVol entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, PeriodeDeVol $periodeDeVol)
    {
        $form = $this->createDeleteForm($periodeDeVol);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($periodeDeVol);
            $em->flush($periodeDeVol);
        }

        return $this->redirectToRoute('periodedevol_index');
    }

    /**
     * Creates a form to delete a periodeDeVol entity.
     *
     * @param PeriodeDeVol $periodeDeVol The periodeDeVol entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(PeriodeDeVol $periodeDeVol)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('periodedevol_delete', array('id' => $periodeDeVol->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
