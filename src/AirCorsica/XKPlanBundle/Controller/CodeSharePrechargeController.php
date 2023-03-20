<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\CodeSharePrecharge;
use AirCorsica\XKPlanBundle\Entity\CodeInterne;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Codeshareprecharge controller.
 *
 */
class CodeSharePrechargeController extends Controller
{
    /**
     * Lists all codeSharePrecharge entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $codeSharePrecharges = $em->getRepository('AirCorsicaXKPlanBundle:CodeSharePrecharge')->findAll();

        return $this->render('AirCorsicaXKPlanBundle:Codeshareprecharge:index.html.twig', array(
            'codeSharePrecharges' => $codeSharePrecharges,
        ));
    }

    /**
     * Creates a new codeSharePrecharge entity.
     * @Security("has_role('ROLE_ADMIN')")     *
     * @ParamConverter("codeinterne_id", class="AirCorsicaXKPlanBundle:CodeInterne")
     */
    public function newAction(Request $request, CodeInterne $codeinterne_id)
    {
        $codeSharePrecharge = new CodeSharePrecharge();
        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\CodeSharePrechargeType', $codeSharePrecharge, array(
            'attr' => array(
                'idCodeInterne' => $codeinterne_id->getId(),
            )));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($codeSharePrecharge);
            $em->flush($codeSharePrecharge);

            $request->getSession()->getFlashBag()->add('success', 'Code Shares créé avec succès.');

            return $this->redirectToRoute('codeinterne_index');
        }

        return $this->render('AirCorsicaXKPlanBundle:Codeshareprecharge:new.html.twig', array(
            'codeSharePrecharge' => $codeSharePrecharge,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a codeSharePrecharge entity.
     *
     */
    public function showAction(CodeSharePrecharge $codeSharePrecharge)
    {
        $deleteForm = $this->createDeleteForm($codeSharePrecharge);

        return $this->render('AirCorsicaXKPlanBundle:Codeshareprecharge:show.html.twig', array(
            'codeSharePrecharge' => $codeSharePrecharge,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing codeSharePrecharge entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, CodeSharePrecharge $codeSharePrecharge)
    {
        $deleteForm = $this->createDeleteForm($codeSharePrecharge);
        $editForm = $this->createForm('AirCorsica\XKPlanBundle\Form\CodeSharePrechargeType', $codeSharePrecharge);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $request->getSession()->getFlashBag()->add('success', 'Code Shares modifié avec succès.');

            return $this->redirectToRoute('codeinterne_index');
        }

        return $this->render('AirCorsicaXKPlanBundle:Codeshareprecharge:edit.html.twig', array(
            'codeSharePrecharge' => $codeSharePrecharge,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a codeSharePrecharge entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, CodeSharePrecharge $codeSharePrecharge)
    {
        /*$form = $this->createDeleteForm($codeSharePrecharge);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($codeSharePrecharge);
            $em->flush($codeSharePrecharge);
        }*/

        $em = $this->getDoctrine()->getManager();

        $CodeSharePrecharge = $em->getRepository('AirCorsicaXKPlanBundle:CodeSharePrecharge')->find($codeSharePrecharge);

        if (!$CodeSharePrecharge) {
            throw $this->createNotFoundException('Ce code share n\'existe pas');
        }
        $em->remove($CodeSharePrecharge);
        $em->flush($CodeSharePrecharge);

        $request->getSession()->getFlashBag()->add('success', 'Code Shares supprimé avec succès.');

        return $this->redirectToRoute('codeinterne_index');
    }

    /**
     * Creates a form to delete a codeSharePrecharge entity.
     *
     * @param CodeSharePrecharge $codeSharePrecharge The codeSharePrecharge entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(CodeSharePrecharge $codeSharePrecharge)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('codeshareprecharge_delete', array('id' => $codeSharePrecharge->getId())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}
