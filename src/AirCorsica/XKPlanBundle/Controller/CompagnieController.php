<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\Compagnie;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Compagnie controller.
 *
 */
class CompagnieController extends Controller
{
    /**
     * Lists all compagnie entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $compagnies = $em->getRepository('AirCorsicaXKPlanBundle:Compagnie')->findBy(array(),array('nom' => 'ASC'));

        return $this->render('AirCorsicaXKPlanBundle:Compagnie:index.html.twig', array(
            'compagnies' => $compagnies,
        ));
    }

    /**
     * Creates a new compagnie entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $compagnie = new Compagnie();
        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\CompagnieType', $compagnie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($compagnie);
            $em->flush($compagnie);

            $request->getSession()->getFlashBag()->add('success', 'Compagnie créée avec succès.');

            return $this->redirectToRoute('compagnie_index');
        }

        return $this->render('AirCorsicaXKPlanBundle:Compagnie:new.html.twig', array(
            'compagnie' => $compagnie,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a compagnie entity.
     *
     */
    public function showAction(Compagnie $compagnie)
    {
        $deleteForm = $this->createDeleteForm($compagnie);

        return $this->render('AirCorsicaXKPlanBundle:Compagnie:show.html.twig', array(
            'compagnie' => $compagnie,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing compagnie entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, Compagnie $compagnie)
    {
        $deleteForm = $this->createDeleteForm($compagnie);
        $editForm = $this->createForm('AirCorsica\XKPlanBundle\Form\CompagnieType', $compagnie);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $request->getSession()->getFlashBag()->add('success', 'Compagnie modifiée avec succès.');

            return $this->redirectToRoute('compagnie_edit', array('id' => $compagnie->getId()));
        }

        return $this->render('AirCorsicaXKPlanBundle:Compagnie:edit.html.twig', array(
            'compagnie' => $compagnie,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a compagnie entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, Compagnie $compagnie)
    {
        /*$form = $this->createDeleteForm($compagnie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($compagnie);
            $em->flush($compagnie);
        }*/

        $em = $this->getDoctrine()->getManager();

        $Compagnie = $em->getRepository('AirCorsicaXKPlanBundle:Compagnie')->find($compagnie);

        if (!$Compagnie) {
            throw $this->createNotFoundException('Cette compagnie n\'existe pas');
        }
        $em->remove($Compagnie);
        $em->flush($Compagnie);

        $request->getSession()->getFlashBag()->add('success', 'Compagnie supprimée avec succès.');

        return $this->redirectToRoute('compagnie_index');
    }

    /**
     * suppression en masse entities.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deletemasseAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoCompagnie = $em->getRepository('AirCorsicaXKPlanBundle:Compagnie');
        $aIdCompagnies = explode('_',$request->get('ids_item'));
        foreach ($aIdCompagnies as $aIdCompagnie) {
            $compagnie = $repoCompagnie->find($aIdCompagnie);
            $em->remove($compagnie);
        }
        $this->getDoctrine()->getManager()->flush();

        $request->getSession()->getFlashBag()->add('success', 'Compagnie supprimées avec succès.');

        return $this->redirectToRoute('compagnie_index');
    }

    /**
     * Creates a form to delete a compagnie entity.
     *
     * @param Compagnie $compagnie The compagnie entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Compagnie $compagnie)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('compagnie_delete', array('id' => $compagnie->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
