<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\Pays;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Pays controller.
 *
 */
class PaysController extends Controller
{
    /**
     * Lists all pays entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $tdvs = $em->getRepository('AirCorsicaXKPlanBundle:TempsDeVol')->findBy(array("saison"=>118078));
        $saison= $em->getRepository('AirCorsicaXKPlanBundle:PeriodeSaison')->find(190331);
        $now = new \DateTime();
        foreach ($tdvs as $tdv ){
            if($tdv->getTypeAvion()->getId()==28 || $tdv->getTypeAvion()->getId()==19 || $tdv->getTypeAvion()->getId()==4){
                $newTdv = clone $tdv;
                $newTdv->setSaison($saison);
                $newTdv->setDateTimeModification($now);
                $newTdv->setDateTimeCreation($now);
                $em->persist($newTdv);
            }
        }
        $em->flush();

        $pays = $em->getRepository('AirCorsicaXKPlanBundle:Pays')->findBy(array(),array('libelle' => 'ASC'));

        return $this->render('AirCorsicaXKPlanBundle:Pays:index.html.twig', array(
            'pays' => $pays,
        ));
    }

    /**
     * Creates a new pays entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $pays = new Pays();
        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\PaysType', $pays);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($pays);
            $em->flush($pays);

            $request->getSession()->getFlashBag()->add('success', 'Pays créé avec succès.');

            return $this->redirectToRoute('pays_index');
        }

        return $this->render('AirCorsicaXKPlanBundle:Pays:new.html.twig', array(
            'pays' => $pays,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a pays entity.
     *
     */
    public function showAction(Pays $pays)
    {
        $deleteForm = $this->createDeleteForm($pays);

        return $this->render('AirCorsicaXKPlanBundle:Pays:show.html.twig', array(
            'pays' => $pays,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing pays entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, Pays $pays)
    {
        $deleteForm = $this->createDeleteForm($pays);
        $editForm = $this->createForm('AirCorsica\XKPlanBundle\Form\PaysType', $pays);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $request->getSession()->getFlashBag()->add('success', 'Pays modifié avec succès.');

            return $this->redirectToRoute('pays_edit', array('id' => $pays->getId()));
        }

        return $this->render('AirCorsicaXKPlanBundle:Pays:edit.html.twig', array(
            'pays' => $pays,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a pays entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, Pays $pays)
    {
        /*$form = $this->createDeleteForm($pays);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($pays);
            $em->flush($pays);
        }*/

        $em = $this->getDoctrine()->getManager();

        $pays = $em->getRepository('AirCorsicaXKPlanBundle:Pays')->find($pays);

        if (!$pays) {
            throw $this->createNotFoundException('Ce type d\'avion n\'existe pas');
        }
        $em->remove($pays);
        $em->flush($pays);

        $request->getSession()->getFlashBag()->add('success', 'Pays supprimé avec succès.');

        return $this->redirectToRoute('pays_index');
    }

    /**
     * suppression en masse entities.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deletemasseAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoPays = $em->getRepository('AirCorsicaXKPlanBundle:Pays');
        $aIdPayss = explode('_',$request->get('ids_item'));
        foreach ($aIdPayss as $aIdPays) {
            $pays = $repoPays->find($aIdPays);
            $em->remove($pays);
        }
        $this->getDoctrine()->getManager()->flush();

        $request->getSession()->getFlashBag()->add('success', 'Pays supprimés avec succès.');

        return $this->redirectToRoute('pays_index');
    }

    /**
     * Creates a form to delete a pays entity.
     *
     * @param Pays $pay The pays entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Pays $pays)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('pays_delete', array('id' => $pays->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
