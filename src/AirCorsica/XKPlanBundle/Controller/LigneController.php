<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\Ligne;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
/**
 * Ligne controller.
 *
 */
class LigneController extends Controller
{
    /**
     * Lists all ligne entities.
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

//        $lignes = $em->getRepository('AirCorsicaXKPlanBundle:Ligne')->findAllByOrdre();

        $lignes = array_merge($em->getRepository('AirCorsicaXKPlanBundle:Ligne')->findAllByOrdre(),$em->getRepository('AirCorsicaXKPlanBundle:Ligne')->findAllNonOrdonnee());

        return $this->render('AirCorsicaXKPlanBundle:Ligne:index.html.twig', array(
            'lignes' => $lignes,
        ));
    }

    /**
     * Creates a new ligne entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $ligne = new Ligne();
        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\LigneType', $ligne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($ligne);
            $em->flush($ligne);

            $request->getSession()->getFlashBag()->add('success', 'Ligne créée avec succès.');

            return $this->redirectToRoute('ligne_index');
        }

        return $this->render('AirCorsicaXKPlanBundle:Ligne:new.html.twig', array(
            'ligne' => $ligne,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a ligne entity.
     *
     */
    public function showAction(Ligne $ligne)
    {
        $deleteForm = $this->createDeleteForm($ligne);

        return $this->render('AirCorsicaXKPlanBundle:Ligne:show.html.twig', array(
            'ligne' => $ligne,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing ligne entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, Ligne $ligne)
    {
        $deleteForm = $this->createDeleteForm($ligne);
        $editForm = $this->createForm('AirCorsica\XKPlanBundle\Form\LigneType', $ligne);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $request->getSession()->getFlashBag()->add('success', 'Ligne modifiée avec succès.');

            return $this->redirectToRoute('ligne_edit', array('id' => $ligne->getId()));
        }

        return $this->render('AirCorsicaXKPlanBundle:Ligne:edit.html.twig', array(
            'ligne' => $ligne,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a ligne entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, Ligne $ligne)
    {
        /*$form = $this->createDeleteForm($ligne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($ligne);
            $em->flush($ligne);
        }*/
        $em = $this->getDoctrine()->getManager();

        $Ligne = $em->getRepository('AirCorsicaXKPlanBundle:Ligne')->find($ligne);

        if (!$Ligne) {
            throw $this->createNotFoundException('Cette ligne n\'existe pas');
        }
        $em->remove($Ligne);
        $em->flush($Ligne);

        $request->getSession()->getFlashBag()->add('success', 'Ligne supprimée avec succès.');

        return $this->redirectToRoute('ligne_index');
    }

    /**
     * suppression en masse entities.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deletemasseAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoLigne = $em->getRepository('AirCorsicaXKPlanBundle:Ligne');
        $aIdLignes = explode('_',$request->get('ids_item'));
        foreach ($aIdLignes as $aIdLigne) {
            $ligne = $repoLigne->find($aIdLigne);
            $em->remove($ligne);
        }
        $this->getDoctrine()->getManager()->flush();

        $request->getSession()->getFlashBag()->add('success', 'Lignes supprimées avec succès.');

        return $this->redirectToRoute('ligne_index');
    }

    /**
     * Creates a form to delete a ligne entity.
     *
     * @param Ligne $ligne The ligne entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Ligne $ligne)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('ligne_delete', array('id' => $ligne->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
