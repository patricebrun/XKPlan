<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\Affretement;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Affretement controller.
 *
 */
class AffretementController extends Controller
{
    /**
     * Lists all affretement entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $affretements = $em->getRepository('AirCorsicaXKPlanBundle:Affretement')->findAll();

        return $this->render('AirCorsicaXKPlanBundle:Affretement:index.html.twig', array(
            'affretements' => $affretements,
        ));
    }

    /**
     * Creates a new affretement entity.
     * @Security("has_role('ROLE_ADMIN')")
     *
     */
    public function newAction(Request $request)
    {
        $affretement = new Affretement();
        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\AffretementType', $affretement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($affretement);
            $em->flush($affretement);

            $request->getSession()->getFlashBag()->add('success', 'Affrètement créé avec succès.');

            return $this->redirectToRoute('affretement_index');
        }

        return $this->render('AirCorsicaXKPlanBundle:Affretement:new.html.twig', array(
            'affretement' => $affretement,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a affretement entity.
     *
     */
    public function showAction(Affretement $affretement)
    {
        $deleteForm = $this->createDeleteForm($affretement);

        return $this->render('AirCorsicaXKPlanBundle:Affretement:show.html.twig', array(
            'affretement' => $affretement,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing affretement entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, Affretement $affretement)
    {
        $deleteForm = $this->createDeleteForm($affretement);
        $editForm = $this->createForm('AirCorsica\XKPlanBundle\Form\AffretementType', $affretement);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $request->getSession()->getFlashBag()->add('success', 'Affrètement modifié avec succès.');

            return $this->redirectToRoute('affretement_edit', array('id' => $affretement->getId()));
        }

        return $this->render('AirCorsicaXKPlanBundle:Affretement:edit.html.twig', array(
            'affretement' => $affretement,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a affretement entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, Affretement $affretement)
    {
        /*$form = $this->createDeleteForm($affretement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($affretement);
            $em->flush($affretement);
        }*/

        $em = $this->getDoctrine()->getManager();

        $Affretement = $em->getRepository('AirCorsicaXKPlanBundle:Affretement')->find($affretement);

        if (!$Affretement) {
            throw $this->createNotFoundException('Cet affretement n\'existe pas');
        }
        $em->remove($Affretement);
        $em->flush($Affretement);

        $request->getSession()->getFlashBag()->add('success', 'Affretement supprimé avec succès.');

        return $this->redirectToRoute('affretement_index');
    }

    /**
     * suppression en masse entities.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deletemasseAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAffretement = $em->getRepository('AirCorsicaXKPlanBundle:Affretement');
        $aIdAffretements = explode('_',$request->get('ids_item'));
        foreach ($aIdAffretements as $aIdAffretement) {
            $affretement = $repoAffretement->find($aIdAffretement);
            $em->remove($affretement);
        }
        $this->getDoctrine()->getManager()->flush();

        $request->getSession()->getFlashBag()->add('success', 'Affretement supprimés avec succès.');

        return $this->redirectToRoute('affretement_index');
    }

    /**
     * Creates a form to delete a affretement entity.
     *
     * @param Affretement $affretement The affretement entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Affretement $affretement)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('affretement_delete', array('id' => $affretement->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
