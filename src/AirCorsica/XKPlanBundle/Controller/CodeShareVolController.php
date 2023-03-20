<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\CodeShareVol;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Codesharevol controller.
 *
 */
class CodeShareVolController extends Controller
{
    /**
     * Lists all codeShareVol entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $codeShareVols = $em->getRepository('AirCorsicaXKPlanBundle:CodeShareVol')->findAll();

        return $this->render('AirCorsicaXKPlanBundle:Codesharevol:index.html.twig', array(
            'codeShareVols' => $codeShareVols,
        ));
    }


    /**
     * Creates a new codeShareVol entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $codeShareVol = new Codesharevol();
        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\CodeShareVolType', $codeShareVol);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($codeShareVol);
            $em->flush($codeShareVol);

            return $this->redirectToRoute('codesharevol_index');
        }

        return $this->render('AirCorsicaXKPlanBundle:Codesharevol:new.html.twig', array(
            'codeShareVol' => $codeShareVol,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a codeShareVol entity.
     *
     */
    public function showAction(CodeShareVol $codeShareVol)
    {
        $deleteForm = $this->createDeleteForm($codeShareVol);

        return $this->render('codesharevol/show.html.twig', array(
            'codeShareVol' => $codeShareVol,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing codeShareVol entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, CodeShareVol $codeShareVol)
    {
        $deleteForm = $this->createDeleteForm($codeShareVol);
        $editForm = $this->createForm('AirCorsica\XKPlanBundle\Form\CodeShareVolType', $codeShareVol);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('codesharevol_edit', array('id' => $codeShareVol->getId()));
        }

        return $this->render('codesharevol/edit.html.twig', array(
            'codeShareVol' => $codeShareVol,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a codeShareVol entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, CodeShareVol $codeShareVol)
    {
        $form = $this->createDeleteForm($codeShareVol);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($codeShareVol);
            $em->flush($codeShareVol);
        }

        return $this->redirectToRoute('codesharevol_index');
    }

    /**
     * Creates a form to delete a codeShareVol entity.
     *
     * @param CodeShareVol $codeShareVol The codeShareVol entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(CodeShareVol $codeShareVol)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('codesharevol_delete', array('id' => $codeShareVol->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
