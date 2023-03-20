<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\ModeleSousPeriode;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
/**
 * Modelesousperiode controller.
 *
 */
class ModeleSousPeriodeController extends Controller
{
    /**
     * Lists all modeleSousPeriode entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $modeleSousPeriodes = $em->getRepository('AirCorsicaXKPlanBundle:ModeleSousPeriode')->findAll();

        return $this->render('modelesousperiode/index.html.twig', array(
            'modeleSousPeriodes' => $modeleSousPeriodes,
        ));
    }

    /**
     * Creates a new modeleSousPeriode entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $modeleSousPeriode = new Modelesousperiode();
        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\ModeleSousPeriodeType', $modeleSousPeriode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($modeleSousPeriode);
            $em->flush($modeleSousPeriode);

            return $this->redirectToRoute('modelesousperiode_show', array('id' => $modeleSousPeriode->getId()));
        }

        return $this->render('modelesousperiode/new.html.twig', array(
            'modeleSousPeriode' => $modeleSousPeriode,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a modeleSousPeriode entity.
     *
     */
    public function showAction(ModeleSousPeriode $modeleSousPeriode)
    {
        $deleteForm = $this->createDeleteForm($modeleSousPeriode);

        return $this->render('modelesousperiode/show.html.twig', array(
            'modeleSousPeriode' => $modeleSousPeriode,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing modeleSousPeriode entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, ModeleSousPeriode $modeleSousPeriode)
    {
        $deleteForm = $this->createDeleteForm($modeleSousPeriode);
        $editForm = $this->createForm('AirCorsica\XKPlanBundle\Form\ModeleSousPeriodeType', $modeleSousPeriode);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('modelesousperiode_edit', array('id' => $modeleSousPeriode->getId()));
        }

        return $this->render('modelesousperiode/edit.html.twig', array(
            'modeleSousPeriode' => $modeleSousPeriode,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a modeleSousPeriode entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, ModeleSousPeriode $modeleSousPeriode)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($modeleSousPeriode);
        $em->flush($modeleSousPeriode);

        return $this->redirectToRoute('saison_index');
    }

    /**
     * Creates a form to delete a modeleSousPeriode entity.
     *
     * @param ModeleSousPeriode $modeleSousPeriode The modeleSousPeriode entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(ModeleSousPeriode $modeleSousPeriode)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('modelesousperiode_delete', array('id' => $modeleSousPeriode->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * Creates a new modeleperiode entity from modal.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newFromModalAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $modelesousPeriode = new ModeleSousPeriode();
        $modelesousPeriode->setNom($request->request->get('nomModeleSousPeriode'));
        if($request->request->has('application-ete')){
            $modelesousPeriode->setPourPeriodeEstivalle(1);
        }
        if($request->request->has('application-hiver')){
            $modelesousPeriode->setPourPeriodeHivernalle(1);
        }
        $em->persist($modelesousPeriode);
        $em->flush($modelesousPeriode);

        return $this->redirectToRoute('saison_index');
    }


    /**
     * Displays a form to edit an existing modeleSousPeriode entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editfromModalAction(Request $request, ModeleSousPeriode $modeleSousPeriode)
    {
        $modeleSousPeriode->setNom($request->request->get('nomModeleSousPeriode'));
        if($request->request->has('application-ete')){
            $modeleSousPeriode->setPourPeriodeEstivalle(1);
        }else{
            $modeleSousPeriode->setPourPeriodeEstivalle(0);
        }
        if($request->request->has('application-hiver')){
            $modeleSousPeriode->setPourPeriodeHivernalle(1);
        }else{
            $modeleSousPeriode->setPourPeriodeHivernalle(0);
        }
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('saison_index');
    }
}
