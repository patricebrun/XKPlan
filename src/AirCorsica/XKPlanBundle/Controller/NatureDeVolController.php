<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\NatureDeVol;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Naturedevol controller.
 *
 */
class NatureDeVolController extends Controller
{
    /**
     * Lists all natureDeVol entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        //$natureDeVols = $em->getRepository('AirCorsicaXKPlanBundle:NatureDeVol')->findAll();
        $natureDeVols = $em->getRepository('AirCorsicaXKPlanBundle:NatureDeVol')->findBy(array(), array('ordre' => 'ASC'));

        return $this->render('AirCorsicaXKPlanBundle:Naturedevol:index.html.twig', array(
            'natureDeVols' => $natureDeVols,
        ));
    }

    /**
     * Creates a new natureDeVol entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $natureDeVol = new Naturedevol();
        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\NatureDeVolType', $natureDeVol);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($natureDeVol);
            $em->flush($natureDeVol);

            $request->getSession()->getFlashBag()->add('success', 'Nature de Vol créée avec succès.');

            return $this->redirectToRoute('naturedevol_index');
        }

        return $this->render('AirCorsicaXKPlanBundle:Naturedevol:new.html.twig', array(
            'natureDeVol' => $natureDeVol,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a natureDeVol entity.
     *
     */
    public function showAction(NatureDeVol $natureDeVol)
    {
        $deleteForm = $this->createDeleteForm($natureDeVol);

        return $this->render('AirCorsicaXKPlanBundle:Naturedevol:show.html.twig', array(
            'natureDeVol' => $natureDeVol,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing natureDeVol entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, NatureDeVol $natureDeVol)
    {
        $deleteForm = $this->createDeleteForm($natureDeVol);
        $editForm = $this->createForm('AirCorsica\XKPlanBundle\Form\NatureDeVolType', $natureDeVol);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $request->getSession()->getFlashBag()->add('success', 'Nature de Vol modifiée avec succès.');

            return $this->redirectToRoute('naturedevol_edit', array('id' => $natureDeVol->getId()));
        }

        return $this->render('AirCorsicaXKPlanBundle:Naturedevol:edit.html.twig', array(
            'natureDeVol' => $natureDeVol,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a natureDeVol entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, NatureDeVol $natureDeVol)
    {
        /*$form = $this->createDeleteForm($natureDeVol);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($natureDeVol);
            $em->flush($natureDeVol);
        }*/

        $em = $this->getDoctrine()->getManager();

        $NatureDeVol = $em->getRepository('AirCorsicaXKPlanBundle:NatureDeVol')->find($natureDeVol);

        if (!$NatureDeVol) {
            throw $this->createNotFoundException('Cette nature de vol n\'existe pas');
        }
        $em->remove($NatureDeVol);
        $em->flush($NatureDeVol);

        $request->getSession()->getFlashBag()->add('success', 'Nature de Vol supprimée avec succès.');

        return $this->redirectToRoute('naturedevol_index');
    }

    /**
     * suppression en masse entities.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deletemasseAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoNatureDeVol = $em->getRepository('AirCorsicaXKPlanBundle:NatureDeVol');
        $aIdNatureDeVols = explode('_',$request->get('ids_item'));
        foreach ($aIdNatureDeVols as $aIdNatureDeVol) {
            $naturedevol = $repoNatureDeVol->find($aIdNatureDeVol);
            $em->remove($naturedevol);
        }
        $this->getDoctrine()->getManager()->flush();

        $request->getSession()->getFlashBag()->add('success', 'Nature de Vol supprimées avec succès.');

        return $this->redirectToRoute('naturedevol_index');
    }

    /**
     * Creates a form to delete a natureDeVol entity.
     *
     * @param NatureDeVol $natureDeVol The natureDeVol entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(NatureDeVol $natureDeVol)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('naturedevol_delete', array('id' => $natureDeVol->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
