<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\Avion;
use AirCorsica\XKPlanBundle\Entity\PeriodeImmobilisation;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Avion controller.
 *
 */
class AvionController extends Controller
{
    /**
     * Lists all avion entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        //$avions = $em->getRepository('AirCorsicaXKPlanBundle:Avion')->findAll();
        $avions = $em->getRepository('AirCorsicaXKPlanBundle:Avion')->findAllTrieParAffreteOrdreNom();

        return $this->render('AirCorsicaXKPlanBundle:Avion:index.html.twig', array(
            'avions' => $avions,
        ));
    }

    /**
     * Creates a new avion entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $avion = new Avion();

        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\AvionType', $avion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($avion);
            $em->flush($avion);

            $request->getSession()->getFlashBag()->add('success', 'Avion créé avec succès.');

            return $this->redirectToRoute('avion_index');
        }

        return $this->render('AirCorsicaXKPlanBundle:Avion:new.html.twig', array(
            'avion' => $avion,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a avion entity.
     *
     */
    public function showAction(Avion $avion)
    {
        $deleteForm = $this->createDeleteForm($avion);

        return $this->render('AirCorsicaXKPlanBundle:Avion:show.html.twig', array(
            'avion' => $avion,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing avion entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, Avion $avion)
    {
        $em = $this->getDoctrine()->getManager();
        $originalPeriodeImmo = new ArrayCollection();
        $em = $this->getDoctrine()->getManager();

        $repoTemplate = $em->getRepository('AirCorsicaXKPlanBundle:Template');
        $templateCurrent = $repoTemplate->find($this->get('session')->get('template'));

        $PeriodeImmo = $em->getRepository('AirCorsicaXKPlanBundle:PeriodeImmobilisation');
        $aPI = $PeriodeImmo->getImmoForAvionAndTemplate($avion,$templateCurrent);
        foreach ($aPI as $periodeImmo){
            $originalPeriodeImmo->add($periodeImmo);
        }

        $deleteForm = $this->createDeleteForm($avion);
        $editForm = $this->createForm('AirCorsica\XKPlanBundle\Form\AvionType', $avion);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            /** @var PeriodeImmobilisation $pi */
            foreach ($avion->getPeriodesImmobilsation() as $pi){
                if($pi->getTemplate() == NULL){
                    $pi->setTemplate($templateCurrent);
                    $em->persist($pi);
                }
            }

            /** @var PeriodeImmobilisation $periodeImmo */
            foreach ($originalPeriodeImmo as $periodeImmo){
                $aPi = $avion->getPeriodesImmobilsationWithTemplate($em,$templateCurrent);
                $perImmo = new ArrayCollection();
                foreach ($aPi as $toto){
                    if($toto->getAvion() != null) { //correction bug lorsqu'une période immo est effacé du formualire par le js l'avion prends null comme valeur
                        $perImmo->add($toto);
                    }
                }
                if(false === $perImmo->contains($periodeImmo)){

                    $periodeImmo->setAvion(null);
                    $em->remove($periodeImmo);
                }
            }

            $this->getDoctrine()->getManager()->flush();

            $request->getSession()->getFlashBag()->add('success', 'Avion modifié avec succès.');

            return $this->redirectToRoute('avion_edit', array('id' => $avion->getId()));
        }

        return $this->render('AirCorsicaXKPlanBundle:Avion:edit.html.twig', array(
            'avion' => $avion,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a avion entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, Avion $avion)
    {
        /*$form = $this->createDeleteForm($avion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($avion);
            $em->flush($avion);
        }*/

        $em = $this->getDoctrine()->getManager();

        $Avion = $em->getRepository('AirCorsicaXKPlanBundle:Avion')->find($avion);

        if (!$Avion) {
            throw $this->createNotFoundException('Ce type d\'avion n\'existe pas');
        }
        $em->remove($Avion);
        $em->flush($Avion);

        $request->getSession()->getFlashBag()->add('success', 'Avion supprimé avec succès.');

        return $this->redirectToRoute('avion_index');
    }

    /**
     * suppression en masse entities.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deletemasseAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAvion = $em->getRepository('AirCorsicaXKPlanBundle:Avion');
        $aIdAvions = explode('_',$request->get('ids_item'));
        foreach ($aIdAvions as $aIdAvion) {
            $avion = $repoAvion->find($aIdAvion);
            $em->remove($avion);
        }
        $this->getDoctrine()->getManager()->flush();

        $request->getSession()->getFlashBag()->add('success', 'Avions supprimés avec succès.');

        return $this->redirectToRoute('avion_index');
    }

    /**
     * Creates a form to delete a avion entity.
     *
     * @param Avion $avion The avion entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Avion $avion)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('avion_delete', array('id' => $avion->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
