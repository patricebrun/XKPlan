<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\TypeDeVol;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Typedevol controller.
 *
 */
class TypeDeVolController extends Controller
{
    /**
     * Lists all typeDeVol entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $typeDeVols = $em->getRepository('AirCorsicaXKPlanBundle:TypeDeVol')->findAll();

        return $this->render('AirCorsicaXKPlanBundle:Typedevol:index.html.twig', array(
            'typeDeVols' => $typeDeVols,
        ));
    }

    /**
     * Creates a new typeDeVol entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $typeDeVol = new Typedevol();
        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\TypeDeVolType', $typeDeVol);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($typeDeVol);
            $em->flush($typeDeVol);

            $request->getSession()->getFlashBag()->add('success', 'Type de Vol créé avec succès.');

            return $this->redirectToRoute('typedevol_index');
        }

        return $this->render('AirCorsicaXKPlanBundle:Typedevol:new.html.twig', array(
            'typeDeVol' => $typeDeVol,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a typeDeVol entity.
     *
     */
    public function showAction(TypeDeVol $typeDeVol)
    {
        $deleteForm = $this->createDeleteForm($typeDeVol);

        return $this->render('AirCorsicaXKPlanBundle:Typedevol:show.html.twig', array(
            'typeDeVol' => $typeDeVol,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing typeDeVol entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, TypeDeVol $typeDeVol)
    {
        $deleteForm = $this->createDeleteForm($typeDeVol);
        $editForm = $this->createForm('AirCorsica\XKPlanBundle\Form\TypeDeVolType', $typeDeVol);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $request->getSession()->getFlashBag()->add('success', 'Type de Vol modifié avec succès.');

            return $this->redirectToRoute('typedevol_edit', array('id' => $typeDeVol->getId()));
        }

        return $this->render('AirCorsicaXKPlanBundle:Typedevol:edit.html.twig', array(
            'typeDeVol' => $typeDeVol,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a typeDeVol entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, TypeDeVol $typeDeVol)
    {
        /*$form = $this->createDeleteForm($typeDeVol);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($typeDeVol);
            $em->flush($typeDeVol);
        }*/
        $em = $this->getDoctrine()->getManager();

        $type_De_Vol = $em->getRepository('AirCorsicaXKPlanBundle:TypeDeVol')->find($typeDeVol);

        if (!$type_De_Vol) {
            throw $this->createNotFoundException('Ce type de vol n\'existe pas');
        }
        $em->remove($type_De_Vol);
        $em->flush($type_De_Vol);

        $request->getSession()->getFlashBag()->add('success', 'Type de Vol supprimé avec succès.');

        return $this->redirectToRoute('typedevol_index');
    }

    /**
     * suppression en masse entities.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deletemasseAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoTypeDeVol = $em->getRepository('AirCorsicaXKPlanBundle:TypeDeVol');
        $aIdTypeDeVols = explode('_',$request->get('ids_item'));
        foreach ($aIdTypeDeVols as $aIdTypeDeVol) {
            $typedevol = $repoTypeDeVol->find($aIdTypeDeVol);
            $em->remove($typedevol);
        }
        $this->getDoctrine()->getManager()->flush();

        $request->getSession()->getFlashBag()->add('success', 'Type de Vols supprimés avec succès.');

        return $this->redirectToRoute('typedevol_index');
    }

    /**
     * Creates a form to delete a typeDeVol entity.
     *
     * @param TypeDeVol $typeDeVol The typeDeVol entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(TypeDeVol $typeDeVol)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('typedevol_delete', array('id' => $typeDeVol->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
