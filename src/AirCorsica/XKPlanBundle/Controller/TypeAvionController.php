<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\TypeAvion;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Typeavion controller.
 *
 */
class TypeAvionController extends Controller
{
    /**
     * Lists all typeAvion entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $typeAvions = $em->getRepository('AirCorsicaXKPlanBundle:TypeAvion')->findBy(array(),array('version' => 'ASC'));

        return $this->render('AirCorsicaXKPlanBundle:Typeavion:index.html.twig', array(
            'typeAvions' => $typeAvions,
        ));
    }

    /**
     * Export a codeInterne entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function typeavioncsvAction(){

        $em = $this->getDoctrine()->getManager();

        $typeAvions = $em->getRepository('AirCorsicaXKPlanBundle:TypeAvion')->findBy(array(),array('version' => 'ASC'));

        $response = new StreamedResponse();
        $response->setCallback(function() use($typeAvions){

            $handle = fopen('php://output', 'w+');

            //Champs
            /** @var TypeAvion $typeAvion */
            foreach ($typeAvions as $typeAvion)
            {
                fputcsv($handle,array($typeAvion->getVersion(),$typeAvion->getCodeIATA(),$typeAvion->getCodeOACI(),$typeAvion->getCapaciteSiege(),$typeAvion->getTempsDemiTour()
                ),';');
            }
            fclose($handle);
        }
        );
        $response->setStatusCode(200);
        $now = new \DateTime();
        $response->headers->set('Content-Type', 'text/csv; charset=iso-8859-1');
        $response->headers->set('Content-Disposition','attachment; filename="export_typeAvion_'.$now->format('dmY').'.csv"');

        return $response;
    }

    /**
     * Creates a new typeAvion entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $typeAvion = new Typeavion();
        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\TypeAvionType', $typeAvion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($typeAvion);
            $em->flush($typeAvion);

            $request->getSession()->getFlashBag()->add('success', 'Type Avion créé avec succès.');

            return $this->redirectToRoute('typeavion_index');
        }

        return $this->render('AirCorsicaXKPlanBundle:Typeavion:new.html.twig', array(
            'typeAvion' => $typeAvion,
            'form'      => $form->createView(),
        ));
    }

    /**
     * Finds and displays a typeAvion entity.
     *
     */
    public function showAction(TypeAvion $typeAvion)
    {
        //$deleteForm = $this->createDeleteForm($typeAvion);

        return $this->render('AirCorsicaXKPlanBundle:Typeavion:show.html.twig', array(
            'typeAvion' => $typeAvion,
            //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing typeAvion entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, TypeAvion $typeAvion)
    {
        //$deleteForm = $this->createDeleteForm($typeAvion);
        $editForm = $this->createForm('AirCorsica\XKPlanBundle\Form\TypeAvionType', $typeAvion);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $request->getSession()->getFlashBag()->add('success', 'Type Avion modifié avec succès.');

            return $this->redirectToRoute('typeavion_edit', array('id' => $typeAvion->getId()));
        }

        $em = $this->getDoctrine()->getManager();
        $repoSaison = $em->getRepository('AirCorsicaXKPlanBundle:Saison');
        $repoLignes = $em->getRepository('AirCorsicaXKPlanBundle:Ligne');

        $repoAeroport = $em->getRepository('AirCorsicaXKPlanBundle:Aeroport');
        $aeroports = $repoAeroport->findAll();

        return $this->render('AirCorsicaXKPlanBundle:Typeavion:edit.html.twig', array(
            'typeAvion' => $typeAvion,
            'edit_form' => $editForm->createView(),
            'saisons'   => $repoSaison->findAll(),
            'lignes'    => $repoLignes->findAll(),
            'aeroports' => $aeroports,
            //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a typeAvion entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, TypeAvion $typeAvion)
    {
        $em = $this->getDoctrine()->getManager();

        $type_avion = $em->getRepository('AirCorsicaXKPlanBundle:TypeAvion')->find($typeAvion);

        if (!$type_avion) {
            throw $this->createNotFoundException('Ce type d\'avion n\'existe pas');
        }
        $em->remove($typeAvion);
        $em->flush($typeAvion);

        $request->getSession()->getFlashBag()->add('success', 'Type Avion supprimé avec succès.');

        return $this->redirectToRoute('typeavion_index');
    }

    /**
     * suppression en masse entities.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deletemasseAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoTypeAvion = $em->getRepository('AirCorsicaXKPlanBundle:TypeAvion');
        $aIdTypeAvions = explode('_',$request->get('ids_item'));
        foreach ($aIdTypeAvions as $aIdTypeAvion) {
            $type_avion = $repoTypeAvion->find($aIdTypeAvion);
            $em->remove($type_avion);
        }
        $this->getDoctrine()->getManager()->flush();

        $request->getSession()->getFlashBag()->add('success', 'Type avion supprimés avec succès.');

        return $this->redirectToRoute('typeavion_index');
    }

    /**
     * supprime tous le TDVL d'un typoe avion
     *
     * @param Request $request
     * @param TypeAvion $typeAvion
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteTDVAction(Request $request, TypeAvion $typeAvion)
    {
        $em = $this->getDoctrine()->getManager();
        $typeAvion->getTempsDeVol()->clear();
        $em->persist($typeAvion);
        $em->flush();

        return $this->redirectToRoute('typeavion_edit',array("id" => $typeAvion->getId()));
    }

    /**
     * Copie des TDVL d'une ligne d'une saison vers une autre saison
     *
     * @param Request $request
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function copieAction(Request $request, TypeAvion $typeAvion){
        $em = $this->getDoctrine()->getManager();

        $periodeSaison = $em->find("AirCorsicaXKPlanBundle:PeriodeSaison",$request->request->get('periode_saison_source'));
        $periodeSaisonCible = $em->find("AirCorsicaXKPlanBundle:PeriodeSaison",$request->request->get('periode_saison_cible'));

        $repoAeroport =$em->getRepository("AirCorsicaXKPlanBundle:Aeroport");

        $aeroportAller = $repoAeroport->find($request->request->get('aeroport_aller_source'));
        $aeroportRetour = $repoAeroport->find($request->request->get('aeroport_retour_source'));

        $repoLigne =$em->getRepository("AirCorsicaXKPlanBundle:Ligne");
        $ligne = $repoLigne->findLigneExistante($aeroportAller,$aeroportRetour);

        $repoTDVL = $em->getRepository("AirCorsicaXKPlanBundle:TempsDeVol");



        $aPeriodesSaison = array();
        if($periodeSaison->getIsIATA()){
            $aPeriodesSaison = $periodeSaison->getSaison()->getPeriodesSaison('IATA');
        }else{
            $aPeriodesSaison[] = $periodeSaison;
        }

        $aPeriodesSaisonCible = array();
        if($periodeSaisonCible->getIsIATA()){
            $aPeriodesSaisonCible = $periodeSaisonCible->getSaison()->getPeriodesSaison('IATA');
        }else{
            $aPeriodesSaisonCible[] = $periodeSaisonCible;
        }

        if(sizeof($aPeriodesSaisonCible) != sizeof($aPeriodesSaison)){
            $request->getSession()->getFlashBag()->add('warning', 'Une erreure s\'est produite. Duplication impossible car le nombre de période SOURCE est différente du nombre de période CIBLE.');
            return $this->redirect($_SERVER['HTTP_REFERER'],'302');
        }

        foreach ($aPeriodesSaison as $index=>$periodeSaison) {
            //suppression de tous les tdvl  pour la saison cible et le type avion current
            $aTdvlRemove = $repoTDVL->findBy(
                array(
                    'saison' => $aPeriodesSaisonCible[$index],
                    'typeAvion' => $typeAvion,
                    'ligne' => $ligne
                )
            );

            foreach ($aTdvlRemove as $tdvlRemove){
                $typeAvion->removeTempsDeVol($tdvlRemove);
            }

            //recherche de tous les temps de vol pour la ligne aller de la saison source et du type avion current
            $tdvlsAller = $repoTDVL->findBy(
                array(
                    'saison' => $periodeSaison,
                    'ligne' => $ligne,
                    'typeAvion' => $typeAvion
                )
            );

            //copie des vols ci-dessus
            foreach ($tdvlsAller as $tdvl) {
                $newTdvl = clone $tdvl;
                $newTdvl->setSaison($aPeriodesSaisonCible[$index]);
                $em->persist($newTdvl);
            }
        }

        //si retour est cocché on copie les vols pour la ligne inverse de la source avec la même méthode que ci-dessus
        if($request->request->has('retour_source')){
            //recherche de la ligne inverse à la source
            $repoLigne = $em->getRepository("AirCorsicaXKPlanBundle:Ligne");
            $ligneInverse = $repoLigne->findLigneExistante($ligne->getAeroportArrivee(),$ligne->getAeroportDepart());

            //si la ligne inverse existe alors on copie les tdvl
            if(null ==! $ligneInverse && $ligneInverse != $ligne){

                $aPeriodesSaisonCible = array();
                if($periodeSaisonCible->getIsIATA()){
                    $aPeriodesSaisonCible = $periodeSaisonCible->getSaison()->getPeriodesSaison('IATA');
                }else{
                    $aPeriodesSaisonCible[] = $periodeSaisonCible;
                }

                foreach ($aPeriodesSaison as $index=>$periodeSaison) {
                    //suppression de tous les tdvl  pour la saison cible et le type avion current
                    $aTdvlRemove = $repoTDVL->findBy(
                        array(
                            'saison' => $aPeriodesSaisonCible[$index],
                            'typeAvion' => $typeAvion,
                            'ligne' => $ligneInverse
                        )
                    );

                    foreach ($aTdvlRemove as $tdvlRemove) {
                        $typeAvion->removeTempsDeVol($tdvlRemove);
                    }


                    $aTdvlRetour = $repoTDVL->findBy(
                        array(
                            'saison' => $periodeSaison,
                            'ligne' => $ligneInverse,
                            'typeAvion' => $typeAvion
                        )
                    );

                    foreach ($aTdvlRetour as $tdvlRetour) {
                        $newTdvl = clone $tdvlRetour;
                        $newTdvl->setSaison($aPeriodesSaisonCible[$index]);
                        $newTdvl->setLigne($ligneInverse);
                        $em->persist($newTdvl);
                    }
                }
            }
        }

        $em->flush();

        return $this->redirectToRoute('typeavion_edit',array("id" => $typeAvion->getId()));
    }


    /**
     * Creates a form to delete a typeAvion entity.
     *
     * @param TypeAvion $typeAvion The typeAvion entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(TypeAvion $typeAvion)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('typeavion_delete', array('id' => $typeAvion->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }

    //si besoin redifinition CSV custom
//    public function typeaviontocsvAction(Request $request){
//
//        $em = $this->getDoctrine()->getManager();
//        $atypeAvions = $em->getRepository('AirCorsicaXKPlanBundle:TypeAvion')->findAll();
//
//        $response = new StreamedResponse();
//        $response->setCallback(function() use($atypeAvions){
//
//            $handle = fopen('php://output', 'w+');
//            // Nom des colonnes du CSV
//            fputcsv($handle, array(
//                utf8_decode("Version"),
//                utf8_decode("Code IATA"),
//                utf8_decode("Code OACI"),
//                utf8_decode("Capacité siège"),
//                utf8_decode("Temps de demi-tour"),
//            ),';');
//
//            //Champs
//            /** @var TypeAvion $untypeavion */
//            foreach ($atypeAvions as $untypeavion)
//            {
//
//                    fputcsv($handle,array(
//                        $untypeavion->getVersion(),
//                        $untypeavion->getCodeIATA(),
//                        $untypeavion->getCodeOACI(),
//                        $untypeavion->getCapaciteSiege(),
//                        $untypeavion->getTempsDemiTour(),
//                    ),';');
//
//            }
//            fclose($handle);
//        }
//        );
//        $response->setStatusCode(200);
//        $now = new \DateTime();
//        $response->headers->set('Content-Type', 'text/csv; charset=iso-8859-1');
//        $response->headers->set('Content-Disposition','attachment; filename="export_typesavions_'.$now->format('dmY').'.csv"');
//
//        return $response;
//    }

}
