<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\Aeroport;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Aeroport controller.
 *
 */
class AeroportController extends Controller
{
    /**
     * Lists all aeroport entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $aeroports = $em->getRepository('AirCorsicaXKPlanBundle:Aeroport')->findAll();

        return $this->render('AirCorsicaXKPlanBundle:Aeroport:index.html.twig', array(
            'aeroports' => $aeroports,
        ));
    }

    /**
     * Creates a new aeroport entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $aeroport = new Aeroport();
        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\AeroportType', $aeroport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($aeroport);
            $em->flush($aeroport);

            $request->getSession()->getFlashBag()->add('success', 'Aéroport créé avec succès.');

            return $this->redirectToRoute('aeroport_index');
        }

        return $this->render('AirCorsicaXKPlanBundle:Aeroport:new.html.twig', array(
            'aeroport' => $aeroport,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a aeroport entity.
     *
     */
    public function showAction(Aeroport $aeroport)
    {
        $deleteForm = $this->createDeleteForm($aeroport);

        return $this->render('AirCorsicaXKPlanBundle:Aeroport:show.html.twig', array(
            'aeroport' => $aeroport,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing aeroport entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, Aeroport $aeroport)
    {
        $deleteForm = $this->createDeleteForm($aeroport);
        $editForm = $this->createForm('AirCorsica\XKPlanBundle\Form\AeroportType', $aeroport);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $request->getSession()->getFlashBag()->add('success', 'Aéroport modifié avec succès.');

            return $this->redirectToRoute('aeroport_edit', array('id' => $aeroport->getId()));
        }

        return $this->render('AirCorsicaXKPlanBundle:Aeroport:edit.html.twig', array(
            'aeroport' => $aeroport,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a aeroport entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, Aeroport $aeroport)
    {
        /*$form = $this->createDeleteForm($aeroport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($aeroport);
            $em->flush($aeroport);
        }*/

        $em = $this->getDoctrine()->getManager();

        $Aeroport = $em->getRepository('AirCorsicaXKPlanBundle:Aeroport')->find($aeroport);

        if (!$Aeroport) {
            throw $this->createNotFoundException('Cet aéroport n\'existe pas');
        }
        $em->remove($Aeroport);
        $em->flush($Aeroport);

        $request->getSession()->getFlashBag()->add('success', 'Aéroport supprimé avec succès.');

        return $this->redirectToRoute('aeroport_index');
    }

    /**
     * suppression en masse entities.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deletemasseAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAeroport = $em->getRepository('AirCorsicaXKPlanBundle:Aeroport');
        $aIdAeroports = explode('_',$request->get('ids_item'));
        foreach ($aIdAeroports as $aIdAeroport) {
            $aeroport = $repoAeroport->find($aIdAeroport);
            $em->remove($aeroport);
        }
        $this->getDoctrine()->getManager()->flush();

        $request->getSession()->getFlashBag()->add('success', 'Aéroports supprimés avec succès.');

        return $this->redirectToRoute('aeroport_index');
    }

    /**
     * Creates a form to delete a aeroport entity.
     *
     * @param Aeroport $aeroport The aeroport entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Aeroport $aeroport)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('aeroport_delete', array('id' => $aeroport->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
