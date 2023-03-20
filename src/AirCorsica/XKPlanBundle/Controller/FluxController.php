<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\Flux;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Flux controller.
 *
 */
class FluxController extends Controller
{
    /**
     * Lists all flux entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $fluxes = $em->getRepository('AirCorsicaXKPlanBundle:Flux')->findAll();

        return $this->render('AirCorsicaXKPlanBundle:Flux:index.html.twig', array(
            'fluxes' => $fluxes,
        ));
    }

    /**
     * Creates a new flux entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $flux = new Flux();
        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\FluxType', $flux);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($flux);
            $em->flush($flux);

            return $this->redirectToRoute('flux_index');
        }

        return $this->render('AirCorsicaXKPlanBundle:Flux:new.html.twig', array(
            'flux' => $flux,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a flux entity.
     *
     */
    public function showAction(Flux $flux)
    {
        $deleteForm = $this->createDeleteForm($flux);

        return $this->render('AirCorsicaXKPlanBundle:Flux:show.html.twig', array(
            'flux' => $flux,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing flux entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, Flux $flux)
    {
        $deleteForm = $this->createDeleteForm($flux);
        $editForm = $this->createForm('AirCorsica\XKPlanBundle\Form\FluxType', $flux);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('flux_edit', array('id' => $flux->getId()));
        }

        return $this->render('AirCorsicaXKPlanBundle:Flux:edit.html.twig', array(
            'flux' => $flux,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a flux entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, Flux $flux)
    {
        $form = $this->createDeleteForm($flux);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($flux);
            $em->flush($flux);
        }

        return $this->redirectToRoute('flux_index');
    }

    /**
     * Creates a form to delete a flux entity.
     *
     * @param Flux $flux The flux entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Flux $flux)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('flux_delete', array('id' => $flux->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
