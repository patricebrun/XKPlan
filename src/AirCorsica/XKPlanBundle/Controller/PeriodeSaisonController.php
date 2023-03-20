<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\PeriodeSaison;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Periodesaison controller.
 *
 */
class PeriodeSaisonController extends Controller
{
    /**
     * Lists all periodeSaison entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $periodeSaisons = $em->getRepository('AirCorsicaXKPlanBundle:PeriodeSaison')->findAll();

        return $this->render('AirCorsicaXKPlanBundle:PeriodeSaison:index.html.twig', array(
            'periodeSaisons' => $periodeSaisons,
        ));
    }

    /**
     * Creates a new periodeSaison entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $periodeSaison = new Periodesaison();
        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\PeriodeSaisonType', $periodeSaison);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $periodeSaison->setIsVisible(1);
            $em->persist($periodeSaison);
            $em->flush($periodeSaison);

            return $this->redirectToRoute('periodesaison_show', array('id' => $periodeSaison->getId()));
        }

        return $this->render('AirCorsicaXKPlanBundle:PeriodeSaison:new.html.twig', array(
            'periodeSaison' => $periodeSaison,
            'form' => $form->createView(),vi
        ));
    }

    /**
     * Finds and displays a periodeSaison entity.
     *
     */
    public function showAction(PeriodeSaison $periodeSaison)
    {
        $deleteForm = $this->createDeleteForm($periodeSaison);

        return $this->render('AirCorsicaXKPlanBundle:PeriodeSaison:show.html.twig', array(
            'periodeSaison' => $periodeSaison,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing periodeSaison entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, PeriodeSaison $periodeSaison)
    {
        $deleteForm = $this->createDeleteForm($periodeSaison);
        $editForm = $this->createForm('AirCorsica\XKPlanBundle\Form\PeriodeSaisonType', $periodeSaison);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('periodesaison_edit', array('id' => $periodeSaison->getId()));
        }

        return $this->render('AirCorsicaXKPlanBundle:PeriodeSaison:edit.html.twig', array(
            'periodeSaison' => $periodeSaison,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a periodeSaison entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, PeriodeSaison $periodeSaison)
    {
            $em = $this->getDoctrine()->getManager();
            $em->remove($periodeSaison);
            $em->flush($periodeSaison);

        return $this->redirectToRoute('saison_index');
    }

    /**
     * Creates a form to delete a periodeSaison entity.
     *
     * @param PeriodeSaison $periodeSaison The periodeSaison entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(PeriodeSaison $periodeSaison)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('periodesaison_delete', array('id' => $periodeSaison->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * Creates a new periodeSaison entity from modal.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newFromModalAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $periodeSaison = new PeriodeSaison(
            $request->get('date-debut-periodeSaison'),
            $request->get('date-fin-periodeSaison')
        );
        $saison = $em->find('AirCorsicaXKPlanBundle:Saison',$request->get('saison_id'));
        $periodeSaison->setSaison($saison);
        $periodeSaison->setNom($request->get('nomPeriodeSaison'));
        $periodeSaison->setDescriptif($request->get('descriptifPeriodeSaison'));
        if($request->get('visible_menu')){
            $periodeSaison->setIsVisible(1);
        }else{
            $periodeSaison->setIsVisible(0);
        }

        $em->persist($periodeSaison);
        $em->flush();

        return $this->redirectToRoute('saison_index');
    }


    /**
     * Displays a form to edit an existing periodesaison entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editfromModalAction(Request $request, PeriodeSaison $periodeSaison)
    {
        $periodeSaison->setNom($request->get('nomPeriodeSaison'));
        $periodeSaison->setDateDebut(new \DateTime($request->get('date-debut-periodeSaison')));
        $periodeSaison->setDateFin(new \DateTime($request->get('date-fin-periodeSaison')));
        $periodeSaison->setdescriptif($request->get('descriptifPeriodeSaison'));
        if($request->get('visible_menu')){
            $periodeSaison->setIsVisible(1);
        }else{
            $periodeSaison->setIsVisible(0);
        }
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('saison_index');
    }

}
