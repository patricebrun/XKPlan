<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\CodeInterne;
use AirCorsica\XKPlanBundle\Entity\CodeSharePrecharge;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Codeinterne controller.
 *
 */
class CodeInterneController extends Controller
{
    /**
     * Lists all codeInterne entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $codeInternes = $em->getRepository('AirCorsicaXKPlanBundle:CodeInterne')->findAll();

//        $a=[];
//        foreach ($codeInternes as $codeInterne){
//            foreach ($codeInterne->getCodesShares() as $codeShare){
//                    $a[$codeInterne->getLibelle()][] = $codeShare->getLibelle();
//
//            }
//        }
////        var_dump($a);
//
//        foreach ($a as $codeInterne=>$aCodeShare){
//            $ci = new CodeInterne();
//            $ci->setLibelle($codeInterne);
//            foreach ($aCodeShare as $codeShare){
//                $csp = new CodeSharePrecharge();
//                $csp->setLibelle($codeShare);
//                $csp->setCodeInterne($ci);
//                $ci->addCodesShare($csp);
//                $em->persist($csp);
//            }
//            $em->persist($ci);
//        }
//
//        foreach ($codeInternes as $codeInterne){
//            $em->remove($codeInterne);
//        }
//
//        $em->flush();
//        die;

        return $this->render('AirCorsicaXKPlanBundle:Codeinterne:index.html.twig', array(
            'codeInternes' => $codeInternes,
        ));
    }

    /**
     * Creates a new codeInterne entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $codeInterne = new Codeinterne();
        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\CodeInterneType', $codeInterne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($codeInterne);
            $em->flush($codeInterne);

            $request->getSession()->getFlashBag()->add('success', 'Code Interne créé avec succès.');

            return $this->redirectToRoute('codeinterne_index');
        }

        return $this->render('AirCorsicaXKPlanBundle:Codeinterne:new.html.twig', array(
            'codeInterne' => $codeInterne,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a codeInterne entity.
     *
     */
    public function showAction(CodeInterne $codeInterne)
    {
        $deleteForm = $this->createDeleteForm($codeInterne);

        return $this->render('AirCorsicaXKPlanBundle:Codeinterne:show.html.twig', array(
            'codeInterne' => $codeInterne,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing codeInterne entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, CodeInterne $codeInterne)
    {
        $deleteForm = $this->createDeleteForm($codeInterne);
        $editForm = $this->createForm('AirCorsica\XKPlanBundle\Form\CodeInterneType', $codeInterne);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $request->getSession()->getFlashBag()->add('success', 'Code Interne modifié avec succès.');

            return $this->redirectToRoute('codeinterne_index');
        }

        return $this->render('AirCorsicaXKPlanBundle:Codeinterne:edit.html.twig', array(
            'codeInterne' => $codeInterne,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a codeInterne entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, CodeInterne $codeInterne)
    {
        /*$form = $this->createDeleteForm($codeInterne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($codeInterne);
            $em->flush($codeInterne);
        }*/

        $em = $this->getDoctrine()->getManager();

        $CodeInterne = $em->getRepository('AirCorsicaXKPlanBundle:CodeInterne')->find($codeInterne);

        if (!$CodeInterne) {
            throw $this->createNotFoundException('Ce code interne n\'existe pas');
        }
        $em->remove($CodeInterne);
        $em->flush($CodeInterne);

        $request->getSession()->getFlashBag()->add('success', 'Code Interne supprimé avec succès. Les codes shares associés ont également été supprimés');

        return $this->redirectToRoute('codeinterne_index');
    }

    /**
     * Creates a form to delete a codeInterne entity.
     *
     * @param CodeInterne $codeInterne The codeInterne entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(CodeInterne $codeInterne)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('codeinterne_delete', array('id' => $codeInterne->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * Export a codeInterne entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function codessharecsvAction(){

        $em = $this->getDoctrine()->getManager();

        $codeInternes = $em->getRepository('AirCorsicaXKPlanBundle:CodeInterne')->findAll();

        $response = new StreamedResponse();
        $response->setCallback(function() use($codeInternes){

            $handle = fopen('php://output', 'w+');

            //Champs
            /** @var CodeInterne $codeInterne */
            foreach ($codeInternes as $codeInterne)
            {
                foreach ($codeInterne->getCodesShares() as $codeShare){
                    fputcsv($handle,array($codeInterne->getLibelle(),$codeShare->getLibelle()
                    ),';');
                }
            }
            fclose($handle);
        }
        );
        $response->setStatusCode(200);
        $now = new \DateTime();
        $response->headers->set('Content-Type', 'text/csv; charset=iso-8859-1');
        $response->headers->set('Content-Disposition','attachment; filename="export_codesShare_'.$now->format('dmY').'.csv"');

        return $response;
    }

}
