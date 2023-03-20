<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\ModeleSousPeriode;
use AirCorsica\XKPlanBundle\Entity\Periode;
use AirCorsica\XKPlanBundle\Entity\PeriodeSaison;
use AirCorsica\XKPlanBundle\Entity\Saison;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Saison controller.
 *
 */
class SaisonController extends Controller
{
    /**
     * Lists all saison entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $saisons = $em->getRepository('AirCorsicaXKPlanBundle:Saison')->findAll();
        $modeles = $em->getRepository('AirCorsicaXKPlanBundle:ModeleSousPeriode')->findAll();
        return $this->render('AirCorsicaXKPlanBundle:Saison:index.html.twig', array(
            'saisons' => $saisons,
            'modeles' => $modeles,
        ));
    }

    /**
     * Creates a new saison entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $date = new \DateTime();
        $saison = new Saison($date->format('Y'),"ete");
        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\SaisonType', $saison);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($saison);
            $em->flush($saison);

            return $this->redirectToRoute('saison_show', array('id' => $saison->getId()));
        }

        return $this->render('AirCorsicaXKPlanBundle:Saison:new.html.twig', array(
            'saison' => $saison,
            'form' => $form->createView(),
        ));
    }


    /**
     * Creates a new saison entity from modal.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newFromModalAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $saison = new Saison($request->request->get('annee'),$request->request->get('saison'));
        $periodeSaisonIATA = new PeriodeSaison(
            $saison->getPeriode()->getDateDebut()->format("Y-m-d"),
            $saison->getPeriode()->getDateFin()->format("Y-m-d")
        );
        $periodeSaisonIATA->setNom(
            Saison::getLibelle(
                $request->request->get('annee'),
                $request->request->get('saison')
            ).
            " : Période IATA"
        );
        $periodeSaisonIATA->setIsIATA(true);
        $periodeSaisonIATA->setIsVisible(true);
        $periodeSaisonIATA->setSaison($saison);
        $saison->addPeriodesSaison($periodeSaisonIATA);
        $repoModele = $em->getRepository('AirCorsicaXKPlanBundle:ModeleSousPeriode');
        $modeles = $repoModele->getModeleFromSaison($request->request->get('saison'));

        /** @var ModeleSousPeriode $modele */
        foreach ($modeles as $modele){
            $periodeModele = new PeriodeSaison(
                $saison->getPeriode()->getDateDebut()->format("Y-m-d"),
                $saison->getPeriode()->getDateFin()->format("Y-m-d")
            );
            $periodeModele->setNom(
                Saison::getLibelle(
                    $request->request->get('annee'),
                    $request->request->get('saison')
                ).
                " : ".
                $modele->getNom());
            $periodeModele->setSaison($saison);
            $periodeModele->setIsIATA(false);
            $periodeModele->setIsVisible(true);
            $saison->addPeriodesSaison($periodeModele);
        }

        $em->persist($saison);
        $em->flush();

        return $this->redirectToRoute('saison_index');
    }

    /**
     * @param string $annee "yyyy" ex "2017"
     * @param string $saison  ete | hiver
     * @return JsonResponse
     */
    public function getdatesiataAction($annee,$saison)
    {
        $sAnnee = substr($annee,-2);
        if("ete" == $saison){
            $label = "S";
        }else{
            $label = "W";
        }

        /** @var Periode $periode */
        $periode = Saison::getDateSaisonIATA($annee, $saison);
        $response = new JsonResponse();
        $response->setData(array(
            'dateDebut' => $periode->getDateDebut()->format("d/m/Y"),
            'dateFin'   => $periode->getDateFin()->format("d/m/Y"),
            'code'      =>  $label.$sAnnee,
        ));

        return $response;
    }

    /**
     * Set la visibilié d'une saison dans le menu du datePicker
     * @Security("has_role('ROLE_ADMIN')")
     * @return JsonResponse
     */
    public function setsaisonvisibleAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $saison = $em->getRepository('AirCorsicaXKPlanBundle:Saison')->find($request->get('id'));

        $saison->setVisibleMenuPopup($request->get('visible'));
        $em->persist($saison);
        $em->flush();

        $response = new JsonResponse();

        if($saison->getVisibleMenuPopup() == $request->get('visible')){
            $response->setData(array(
                'valide' => true,
            ));
        }else{
            $response->setData(array(
                'valide' => false,
            ));
        }
        return $response;
    }

    /**
     * Set la visibilié d'une période saison dans le menu du datePicker
     * @Security("has_role('ROLE_ADMIN')")
     * @return JsonResponse
     */
    public function setperiodesaisonvisibleAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $periodeSaison = $em->getRepository('AirCorsicaXKPlanBundle:PeriodeSaison')->find($request->get('id'));

        $periodeSaison->setIsVisible($request->get('visible'));
        $em->persist($periodeSaison);
        $em->flush();

        $response = new JsonResponse();

        if($periodeSaison->getIsVisible() == $request->get('visible')){
            $response->setData(array(
                'valide' => true,
            ));
        }else{
            $response->setData(array(
                'valide' => false,
            ));
        }
        return $response;
    }


    /**
     * Finds and displays a saison entity.
     *
     */
    public function showAction(Saison $saison)
    {
        $deleteForm = $this->createDeleteForm($saison);

        return $this->render('AirCorsicaXKPlanBundle:Saison:show.html.twig', array(
            'saison' => $saison,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing saison entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, Saison $saison)
    {
        $deleteForm = $this->createDeleteForm($saison);
        $editForm = $this->createForm('AirCorsica\XKPlanBundle\Form\SaisonType', $saison);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('saison_edit', array('id' => $saison->getId()));
        }

        return $this->render('AirCorsicaXKPlanBundle:Saison:edit.html.twig', array(
            'saison' => $saison,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a saison entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, Saison $saison)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($saison);
        $em->flush();

        return $this->redirectToRoute('saison_index');
    }

    /**
     * Creates a form to delete a saison entity.
     *
     * @param Saison $saison The saison entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Saison $saison)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('saison_delete', array('id' => $saison->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

}
