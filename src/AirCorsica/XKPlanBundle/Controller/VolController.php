<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\GroupeSITA;
use AirCorsica\XKPlanBundle\Entity\AdresseSITA;
use AirCorsica\XKPlanBundle\Entity\Aeroport;
use AirCorsica\XKPlanBundle\Entity\Ligne;
use AirCorsica\XKPlanBundle\Entity\Erreur\CoherenceDeVolErreur;
use AirCorsica\XKPlanBundle\Entity\Erreur\VolErreurCoherence;
use AirCorsica\XKPlanBundle\Entity\Message\ALTEAMessage;
use AirCorsica\XKPlanBundle\Entity\Message\SCR;
use AirCorsica\XKPlanBundle\Entity\Message\ASMSSM;
use AirCorsica\XKPlanBundle\Entity\Avion;
use AirCorsica\XKPlanBundle\Entity\Compagnie;
use AirCorsica\XKPlanBundle\Entity\Erreur\AbstractErreur;
use AirCorsica\XKPlanBundle\Entity\Erreur\isJoursDeValiditeDansPeriodeErreur;
use AirCorsica\XKPlanBundle\Entity\Erreur\VerifcateurPeriodeErreur;
use AirCorsica\XKPlanBundle\Entity\Message\SSIM;
use AirCorsica\XKPlanBundle\Entity\Periode;
use AirCorsica\XKPlanBundle\Entity\PeriodeDeVol;
use AirCorsica\XKPlanBundle\Entity\PeriodeSaison;
use AirCorsica\XKPlanBundle\Entity\TempsDeVol;
use AirCorsica\XKPlanBundle\Entity\TypeAvion;
use AirCorsica\XKPlanBundle\Entity\Vol;
use AirCorsica\XKPlanBundle\Entity\VolHistorique;
use AirCorsica\XKPlanBundle\Repository\VolRepository;
use AirCorsica\XKPlanBundle\Repository\CodeSharePrechargeRepository;
use AirCorsica\XKPlanBundle\Repository\AbstractCodeShareRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swift_Attachment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Translation\Interval;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Yaml;

/**
 * Vol controller.
 *
 */
class VolController extends Controller
{
    /**
     * Lists all vol entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $vols = $em->getRepository('AirCorsicaXKPlanBundle:Vol')->findAll();

        return $this->render('AirCorsicaXKPlanBundle:Vol:index.html.twig', array(
            'vols' => $vols,
        ));
    }

    /**
    * Editeur vol entities.
    *
    */
    public function editeurAction()
    {
        return $this->render('AirCorsicaXKPlanBundle:Vol:editeur.html.twig');
    }

    /**
     * * verificateur coherence vols
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function verificateurcoherenceAction(Request $request, $export = null)
    {
        //Uniquement pour ce script
        ini_set('max_execution_time', 0);

        $em = $this->getDoctrine()->getManager();
        $selectPeriodeSaison = null;

        $repoTemplate = $em->getRepository('AirCorsicaXKPlanBundle:Template');
        $templateCurrent = $repoTemplate->find($this->get('session')->get('template'));

        if(is_array($request->get("ids_vol"))){
            $aIds = $request->get("ids_vol");
        }else{
            $aIds = explode("_",$request->get("ids_vol"));
        }


        $aReturn = array();
        $saisonTrouvee = false;

        if((!$request->get('date_debut') || !$request->get('date_fin')) && !$request->get('periode_saison')){
            $request->getSession()->getFlashBag()->add('info_coherence', 'Veuillez sélectionner une période saison.');
        }else{
            $aPeriodesSaisons = array();
            if($request->get('periode_saison')){
                $selectPeriodeSaison = $em->getRepository('AirCorsicaXKPlanBundle:PeriodeSaison')->find($request->get('periode_saison'));
                $saisonTrouvee = true;
            }else {
                $aPeriodesSaisons = $em->getRepository('AirCorsicaXKPlanBundle:PeriodeSaison')->getPeriodesSaisons($request->get('date_debut'), $request->get('date_fin'));
                if(sizeof($aPeriodesSaisons) == 0) {
                    $request->getSession()->getFlashBag()->add('info_coherence', 'Veuillez sélectionner une période saison.');
                }else{
                    $saisonTrouvee = true;
                }
            }
        }
        if($export){
            $saisonTrouvee = true;
            $aPeriodesSaisons = array();
        }

        if($saisonTrouvee){
            /** @var PeriodeSaison $periodeSaison */
            foreach ($aPeriodesSaisons as $periodeSaison) {
                if (preg_match("%IATA%", $periodeSaison->getNom())) {
                    $selectPeriodeSaison = $periodeSaison;
                    break;
                }
            }
            if ($selectPeriodeSaison == null && sizeof($aPeriodesSaisons)) {
                //aPeriodesSaisons est ordonné par date de début
                $selectPeriodeSaison = $aPeriodesSaisons[0];
            }

            /** @var VolRepository $repoVol */
            $repoVol = $em->getRepository('AirCorsicaXKPlanBundle:Vol');

            $aVolTraite = array();
            $aVolTraiteUnique = array();
            foreach ($aIds as $idVol){
                /** @var Vol $vol */
                $vol = $repoVol->find($idVol);

                if($vol->getPeriodeDeVol()->getDeleste()){
                    continue;
                }

                if($vol->getLigne() == null){
                    continue;
                }

                $coherenceDeVol = new CoherenceDeVolErreur();
                $coherenceDeVol->setVol($vol);
                if(!$vol->getPeriodeDeVol()->testDecollageAtterissage()){
                    $volErreur = new VolErreurCoherence();
                    $volErreur->addRecommandation(VolErreurCoherence::heureDuVol);
                    $volErreur->setVol($vol);
                    $coherenceDeVol->addVolsErreurs($volErreur);
//                    $coherenceDeVol->aadMessages()
                }

                $maRequest = new Request(array(
                        'date_debut'=> $vol->getPeriodeDeVol()->getDateDebut()->format('d-m-Y'),
                        'date_fin'=> $vol->getPeriodeDeVol()->getDateFin()->format('d-m-Y'),
                        'avion'=> $vol->getAvion(),
                        'jours'=> $vol->getPeriodeDeVol()->getJoursDeValidite(),
                    )
                );

                $aVolsToCompare = $repoVol->getVolsFilter($maRequest,$templateCurrent,"ASC");

                /** @var Vol $volCourant */
                foreach ($aVolsToCompare as $volCourant){
                    if($volCourant->getPeriodeDeVol()->getDeleste()){
                        continue;
                    }

                    if($volCourant->getLigne() == null){
                        continue;
                    }
                    if(array_search($vol->getId()."-".$volCourant->getId(),$aVolTraite) === FALSE){
                        $aVolTraite[] = $vol->getId()."-".$volCourant->getId();
                        $aVolTraite[] = $volCourant->getId()."-".$vol->getId();
                        if($vol->getId() != $volCourant->getId()){
                            if(!$vol->testNonSuperposition($volCourant)) {
                                $volErreur = new VolErreurCoherence();
                                $volErreur->addRecommandation(VolErreurCoherence::superposition);
                                $volErreur->setVol($volCourant);
                                $coherenceDeVol->addVolsErreurs($volErreur);
                            }
                            if(!$volCourant->testDecollageValide($vol)){
                                $volErreur = new VolErreurCoherence();
                                $volErreur->addRecommandation(VolErreurCoherence::demiTour);
                                $volErreur->setVol($volCourant);
                                $coherenceDeVol->addVolsErreurs($volErreur);
                            }
                        }
                    }
                }
//                $maRequest->query->add(array(array('numerodevol',$vol->getNumero())));
                $maRequest->query->set('numerodevol',$vol->getNumero());
                $maRequest->query->remove('avion');
                $aVolsToCompare = $repoVol->getVolsFilter($maRequest,$templateCurrent,"ASC");
                foreach ($aVolsToCompare as $volCourant){
                    if($volCourant->getPeriodeDeVol()->getDeleste()){
                        continue;
                    }

                    if($volCourant->getLigne() == null){
                        continue;
                    }
                    if(array_search($vol->getId()."-".$volCourant->getId(),$aVolTraiteUnique) === FALSE
                        && $vol->getId() != $volCourant->getId()) {
                        $aVolTraiteUnique[] = $vol->getId() . "-" . $volCourant->getId();
                        $aVolTraiteUnique[] = $volCourant->getId() . "-" . $vol->getId();
                        if ($vol->getId() != $volCourant->getId()) {
                            if ($vol->getNumero() == $volCourant->getNumero()) {
                                $volErreur = new VolErreurCoherence();
                                $volErreur->addRecommandation(VolErreurCoherence::unique);
                                $volErreur->setVol($volCourant);
                                $coherenceDeVol->addVolsErreurs($volErreur);
                            }
                        }
                    }
                }

                //Test sens des vols
                /** @var Vol $volPrecedent */
                $volTraite = array();
                $volTraitePrecedent = array();
                for($j=0;$j<7;$j++){
                    if($vol->getPeriodeDeVol()->getJoursDeValidite()[$j] != "-"){
                        $volPrecedentMemeJour = $repoVol->getVolPrecedentMemeJour($vol,$vol->getPeriodeDeVol()->getJoursDeValidite()[$j],$templateCurrent);
                        if(sizeof($volPrecedentMemeJour)==1)
                        {
                            if($volPrecedentMemeJour[0]->getPeriodeDeVol()->getDeleste()){
                                continue;
                            }

                            if($volPrecedentMemeJour[0]->getLigne() == null){
                                continue;
                            }

                            //Le vol precedent est sur deux jours, il ne devrait pas sortir dans la requete ci dessus
                            if($volPrecedentMemeJour[0]->getPeriodeDeVol()->getDateDebut() >=  $vol->getPeriodeDeVol()->getDateDebut()){
                                continue;
                            }

                            if(!in_array($volPrecedentMemeJour[0]->getId(),$volTraite) && $volPrecedentMemeJour[0]->getLigne()->getAeroportArrivee()->getCodeIATA() != $vol->getLigne()->getAeroportDepart()->getCodeIATA()){
                                $volErreur = new VolErreurCoherence();
                                $volErreur->addRecommandation(sprintf(VolErreurCoherence::sens,$vol->getLigne()->getAeroportDepart()->getCodeIATA(),$volPrecedentMemeJour[0]->getLigne()->getAeroportArrivee()->getCodeIATA()));
                                $volErreur->setVol($volPrecedentMemeJour[0]);
                                $coherenceDeVol->addVolsErreurs($volErreur);
                                $volTraite[] = $volPrecedentMemeJour[0]->getId() ;
                            }
                        }
//                        else{
//                            if ($vol->getPeriodeDeVol()->getJoursDeValidite()[$j] == 1) {
//                                $jourValiditePrecedent = 7;
//                            } else {
//                                $jourValiditePrecedent = $vol->getPeriodeDeVol()->getJoursDeValidite()[$j] - 1;
//                            }
//                            $volPrecedenJourPrecedent = $repoVol->getVolPrecedentJourPrecedent($vol, $jourValiditePrecedent, $templateCurrent);
//
//                            if (sizeof($volPrecedenJourPrecedent) == 1) {
//                                if (!in_array($volPrecedenJourPrecedent[0]->getId(),$volTraitePrecedent) && $volPrecedenJourPrecedent[0]->getLigne()->getAeroportArrivee()->getCodeIATA() != $vol->getLigne()->getAeroportDepart()->getCodeIATA()) {
//                                    $volErreur = new VolErreurCoherence();
//                                    $volErreur->addRecommandation(sprintf(VolErreurCoherence::sensPrecedent,$vol->getLigne()->getAeroportDepart()->getCodeIATA(),$volPrecedenJourPrecedent[0]->getLigne()->getAeroportArrivee()->getCodeIATA()));
////                                    $volErreur->addRecommandation(sprintf(VolErreurCoherence::sensPrecedent,$vol->getLigne()->getAeroportDepart()->getCodeIATA(),$volPrecedenJourPrecedent[0]->getLigne()->getAeroportArrivee()->getCodeIATA(),$j+1,$jourValiditePrecedent));
//                                    $volErreur->setVol($volPrecedenJourPrecedent[0]);
//                                    $coherenceDeVol->addVolsErreurs($volErreur);
//                                    $id = $volPrecedenJourPrecedent[0]->getId();
//                                    $volTraitePrecedent[] = $id ;
//                                }
//                            }
//
////                            $periodeDeVolJourPrecedent = $repoVol->getPeriodeDeVolJourPrecedent($vol, $jourValiditePrecedent, $templateCurrent);
////                            if (sizeof($periodeDeVolJourPrecedent) == 1) {
////                                if ($periodeDeVolJourPrecedent[0]->getId() != $id && $periodeDeVolJourPrecedent[0]->getLigne()->getAeroportArrivee()->getCodeIATA() != $vol->getLigne()->getAeroportDepart()->getCodeIATA()) {
////                                    $volErreur = new VolErreurCoherence();
////                                    $date = clone $vol->getPeriodeDeVol()->getDateDebut();
////                                    $volErreur->addRecommandation(sprintf(VolErreurCoherence::sensPeriodePrecedente,$vol->getLigne()->getAeroportDepart()->getCodeIATA(),$periodeDeVolJourPrecedent[0]->getLigne()->getAeroportArrivee()->getCodeIATA()));
//////                                    $volErreur->addRecommandation(sprintf(VolErreurCoherence::sensPeriodePrecedente,$vol->getLigne()->getAeroportDepart()->getCodeIATA(),$volPrecedenJourPrecedent[0]->getLigne()->getAeroportArrivee()->getCodeIATA(),$date->sub(new \DateInterval("P1D"))->format("d/m/Y"),$vol->getPeriodeDeVol()->getDateDebut()->format("d/m/Y")));
////                                    $volErreur->setVol($periodeDeVolJourPrecedent[0]);
////                                    $coherenceDeVol->addVolsErreurs($volErreur);
////                                    break;
////                                }
////                            }
//                        }
                    }
                }

                //Je suis le dernier vol donc personne après moi
                $dateDayDebutVol = $vol->getPeriodeDeVol()->getDecollage();
                $dateDayFinVol = $vol->getPeriodeDeVol()->getAtterissage();
                if($dateDayDebutVol->format("d") != $dateDayFinVol->format("d")){
                    continue;
                }
                $volTraite = array();
                $volTraiteSuivant = array();
                for($j=0;$j<7;$j++){
                    if($vol->getPeriodeDeVol()->getJoursDeValidite()[$j] != "-"){
                        $volProchainMemeJour = $repoVol->getVolProchainMemeJour($vol,$vol->getPeriodeDeVol()->getJoursDeValidite()[$j],$templateCurrent);

                        if(sizeof($volProchainMemeJour)==1){
                            if($volProchainMemeJour[0]->getPeriodeDeVol()->getDeleste()){
                                continue;
                            }

                            if($volProchainMemeJour[0]->getLigne() == null){
                                continue;
                            }

                            //Le vol prochain est sur deux jours, il ne devrait pas sortir dans la requete ci dessus
                            if($volProchainMemeJour[0]->getPeriodeDeVol()->getDateFin() <=  $vol->getPeriodeDeVol()->getDateFin()){
                                continue;
                            }

                            if(!in_array($volProchainMemeJour[0]->getId(),$volTraite) && $volProchainMemeJour[0]->getLigne()->getAeroportDepart()->getCodeIATA() != $vol->getLigne()->getAeroportArrivee()->getCodeIATA()){
                                $volErreur = new VolErreurCoherence();
                                $volErreur->addRecommandation(sprintf(VolErreurCoherence::sens,$volProchainMemeJour[0]->getLigne()->getAeroportDepart()->getCodeIATA(),$vol->getLigne()->getAeroportArrivee()->getCodeIATA()));
                                $volErreur->setVol($volProchainMemeJour[0]);
                                $coherenceDeVol->addVolsErreurs($volErreur);
                                $volTraite[] = $volProchainMemeJour[0]->getId() ;
                            }
                        }
//                        else{
//                            if ($vol->getPeriodeDeVol()->getJoursDeValidite()[$j] == 7) {
//                                $jourValiditeSuivant = 1;
//                            } else {
//                                $jourValiditeSuivant = $vol->getPeriodeDeVol()->getJoursDeValidite()[$j] + 1;
//                            }
//                            $volProchainJourSuivant = $repoVol->getVolProchainJourSuivant($vol, $jourValiditeSuivant, $templateCurrent);
//
//                            if (sizeof($volProchainJourSuivant) == 1) {
//                                if (!in_array($volProchainJourSuivant[0]->getId(),$volTraiteSuivant) && $volProchainJourSuivant[0]->getLigne()->getAeroportDepart()->getCodeIATA() != $vol->getLigne()->getAeroportArrivee()->getCodeIATA()) {
//                                    $volErreur = new VolErreurCoherence();
//                                    $volErreur->addRecommandation(sprintf(VolErreurCoherence::sensSuivant,$volProchainJourSuivant[0]->getLigne()->getAeroportDepart()->getCodeIATA(),$vol->getLigne()->getAeroportArrivee()->getCodeIATA()));
////                                    $volErreur->addRecommandation(sprintf(VolErreurCoherence::sensSuivant,$volProchainJourSuivant[0]->getLigne()->getAeroportDepart()->getCodeIATA(),$vol->getLigne()->getAeroportArrivee()->getCodeIATA(),$j+1,$jourValiditeSuivant));
//                                    $volErreur->setVol($volProchainJourSuivant[0]);
//                                    $coherenceDeVol->addVolsErreurs($volErreur);
//                                    $id = $volProchainJourSuivant[0]->getId();
//                                    $volTraiteSuivant[] = $id ;
//                                }
//                            }
//
////                            $periodeDeVolJourSuivant = $repoVol->getPeriodeDeVolJourSuivant($vol, $jourValiditeSuivant, $templateCurrent);
////                            if (sizeof($periodeDeVolJourSuivant) == 1) {
////                                if ($periodeDeVolJourSuivant[0]->getId() != $id && $periodeDeVolJourSuivant[0]->getLigne()->getAeroportDepart()->getCodeIATA() != $vol->getLigne()->getAeroportArrivee()->getCodeIATA()) {
////                                    $volErreur = new VolErreurCoherence();
//////                                    $date = clone $vol->getPeriodeDeVol()->getDateFin();
////                                    $volErreur->addRecommandation(sprintf(VolErreurCoherence::sensPeriodeSuivante,$periodeDeVolJourSuivant[0]->getLigne()->getAeroportDepart()->getCodeIATA(),$vol->getLigne()->getAeroportArrivee()->getCodeIATA(),$vol->getPeriodeDeVol()->getDateFin()->format("d/m/Y")));
//////                                    $volErreur->addRecommandation(sprintf(VolErreurCoherence::sensPeriodeSuivante,$volProchainJourSuivant[0]->getLigne()->getAeroportDepart()->getCodeIATA(),$vol->getLigne()->getAeroportArrivee()->getCodeIATA(),$vol->getPeriodeDeVol()->getDateFin()->format("d/m/Y"),$date->add(new \DateInterval("P1D"))->format("d/m/Y")));
////                                    $volErreur->setVol($periodeDeVolJourSuivant[0]);
////                                    $coherenceDeVol->addVolsErreurs($volErreur);
//////                                    break;
////                                }
////                            }
//                        }
                    }
                }
                if(sizeof($coherenceDeVol->getAVolsErreurs()))
                {
                    $aReturn[] = $coherenceDeVol;
                }
            }
        }

        $saisons = $em->getRepository('AirCorsicaXKPlanBundle:Saison')->findAll();

        if(isset($export)){
            switch ($export){
                case 'pdf':
                    return $this->renderView('AirCorsicaXKPlanBundle:Vol:coherencevolspdf.html.twig', array(
                        'periodeSaison' => $selectPeriodeSaison,
                        'saisons'   => $saisons,
                        'aCoherence'   => $aReturn,
                        'aIdSelectionne'    => $aIds,
                        'request'   => $request,
                    ));
                    break;
                default:
                    return $this->render('AirCorsicaXKPlanBundle:Vol:coherence_vols.html.twig', array(
                        'periodeSaison' => $selectPeriodeSaison,
                        'saisons'   => $saisons,
                        'aCoherence'   => $aReturn,
                        'aIdSelectionne'    => $aIds,
                        'request'   => $request,
                    ));
            }
        }else{
            return $this->render('AirCorsicaXKPlanBundle:Vol:coherence_vols.html.twig', array(
                'periodeSaison' => $selectPeriodeSaison,
                'saisons'   => $saisons,
                'aCoherence'   => $aReturn,
                'aIdSelectionne'    => $aIds,
                'request'   => $request,
            ));
        }

    }

    /**
     * * verificateur periode vols
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function verificateurperiodeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var VolRepository $repoVol */
        $repoVol = $em->getRepository('AirCorsicaXKPlanBundle:Vol');

        $orderParPeriode = "ASC";

        $repoTemplate = $em->getRepository('AirCorsicaXKPlanBundle:Template');
        $templateCurrent = $repoTemplate->find($this->get('session')->get('template'));
//        $templates = $repoTemplate->findAll();

        $aErreur = array();
        $aCorriger = array();
        if($request->get('appliquer_recommandations')){
            $repoPeriodeDeVol = $em->getRepository('AirCorsicaXKPlanBundle:PeriodeDeVol');
            $aCorriger = $request->get('periodeDeVolId');
            $vols = array();
            foreach ($aCorriger as $idPeriodeDeVol){
                $vols[] = $repoPeriodeDeVol->find($idPeriodeDeVol)->getVol();
            }
        }else{
            $requestOnlyDate = new Request(array(   'filtre_date_debut'=> $request->get('filtre_date_debut'),
                                                    'date_debut'=> $request->get('date_debut'),
                                                    'filtre_date_fin'=> $request->get('filtre_date_fin'),
                                                    'date_fin'=> $request->get('date_fin')
                                                )
                                            );

            $vols = $repoVol->getVolsFilter($requestOnlyDate,$templateCurrent,$orderParPeriode);
        }

        /** @var Vol $vol */
        foreach ($vols as $vol){
            $periodeDeVol = $vol->getPeriodeDeVol();
            $aErreur = array_merge($aErreur,$this->verifierPeriode($periodeDeVol,$aCorriger));
        }

        if(sizeof($aCorriger)){
            return $this->redirect('/vol/liste?'.$request->getQueryString());
        }
        return $this->render('AirCorsicaXKPlanBundle:Vol:verificateurs_periodes.html.twig', array(
            'request'   => $request,
            'aErreur'   => $aErreur
        ));
    }

    /**
     * @param PeriodeDeVol $periodeDeVol
     * @param $aCorriger
     * @return array
     */
    private function verifierPeriode(PeriodeDeVol $periodeDeVol,$aCorriger){
        $aReturn = array();
        $aErreur1 = $this->estCoherente($periodeDeVol,$aCorriger);
        $aErreur2 = $this->contientJoursDeValidite($periodeDeVol,$aCorriger);
        $aErreur3 = $this->isJoursDeValiditeDansPeriode($periodeDeVol,$aCorriger);
        $aReturn = array_merge($aErreur1,$aErreur2,$aErreur3);
        return $aReturn;
    }

    /**
     * test si la date debut et la date de fin correspond à un jour de validite
     * @param PeriodeDeVol $periodeDeVol
     * @param $aCorriger tableau d'id vol à corriger
     * @return array
     */
    private function estCoherente(PeriodeDeVol $periodeDeVol,$aCorriger = array()){
        $aErreur = array();
        $em = $this->getDoctrine()->getManager();
        //parametre TRUE renvoie la période de vol modifié ou TRUE si this est OK
        $test = $periodeDeVol->estCoherente(TRUE);
        if($test instanceof PeriodeDeVol){
            //On applique la correction si demandée
            if(in_array($periodeDeVol->getId(),$aCorriger)){
                $periodeDeVol->getVol()->updatePeriodeDeVol($test);
                $em->flush();
            }else{
                $erreur = new VerifcateurPeriodeErreur($periodeDeVol,$test);
//                $erreur->setObjectErreur($periodeDeVol);
//                $erreur->setObjectCorrige($test);
                if($periodeDeVol->getDateDebut() != $test->getDateDebut()){
                    $erreur->addProbleme("Début");
                }
                if($periodeDeVol->getDateFin() != $test->getDateFin()){
                    $erreur->addProbleme("Fin");
                }
                $erreur->addRecommandation("Réduction recommandée: Certains jours de validité sont absents de l'intervalle de dates.");
                $aErreur[] = $erreur;
            }
        }
        return $aErreur;
    }

    /**
     * On test si la période contient des jours de validite ou bien si les jours de validite sont dans la période
     * @param PeriodeDeVol $periodeDeVol
     * @param $aCorriger tableau d'id vol à corriger
     * @return array
     */
    private function contientJoursDeValidite(PeriodeDeVol $periodeDeVol,$aCorriger = array()){
        $aErreur = array();
        $em = $this->getDoctrine()->getManager();
        $test = $periodeDeVol->contientJoursDeValidite();
        if(!$test){
            if(in_array($periodeDeVol->getId(),$aCorriger)){
                $em->remove($periodeDeVol->getVol());
                $em->flush();
            }else {
//                $erreur = new AbstractErreur();
                //Dans ce cas, pas de correction possible donc le même objet. On propose la destuction de l'objet
                $erreur = new VerifcateurPeriodeErreur($periodeDeVol,$periodeDeVol);
//                $erreur->setObjectErreur($periodeDeVol);

//                $erreur->setObjectCorrige($periodeDeVol);
                $erreur->addRecommandation("Destruction recommandée: Aucun jour de validité n'est inclus dans la période de dates.");
                $erreur->addProbleme("Jours Validités");
                $aErreur[] = $erreur;
            }
        }
        return $aErreur;
    }

    /**
     * On test si les jours de validité sont dans la période et non en dehors
     * @param PeriodeDeVol $periodeDeVol
     * @param $aCorriger tableau d'id vol à corriger
     * @return array
     */
    private function isJoursDeValiditeDansPeriode(PeriodeDeVol $periodeDeVol,$aCorriger = array()){
        $aErreur = array();
        $em = $this->getDoctrine()->getManager();
        //parametre TRUE renvoie la période de vol modifié ou TRUE si this est OK
        $test = $periodeDeVol->isJoursDeValiditeDansPeriode(TRUE);
        if($test instanceof PeriodeDeVol) {
            if(in_array($periodeDeVol->getId(),$aCorriger)){
                $periodeDeVol->updatePeriode($test);
                $em->flush();
            }else {
                $erreur = new VerifcateurPeriodeErreur($periodeDeVol,$test);
//                $erreur->setObjectErreur($periodeDeVol);
//                $erreur->setObjectCorrige($test);
                $erreur->addRecommandation("Réduction recommandée: Certains jours de validité sont absents de l'intervalle de dates.");
                $erreur->addProbleme("Jours Validités");
                $aErreur[] = $erreur;
            }
        }
        return $aErreur;
    }


    /**
     * * Acquitter messages SITA
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function acquitterAction(Request $request)
    {
        $aIdVolMessageAAcquitter = explode ("_",$request->get('arrayIdVolsAAcquitter'));

        $em = $this->getDoctrine()->getManager();

        $i=0;
        foreach( $aIdVolMessageAAcquitter as $unIdVol ) {
            //Modification du vol d'origine avec une
            //nouvelle période de Vol: datedébut identique et datefin le jour précédent le jour transmis
            //------------------------------------------------------------------------------------------
            $vol_{$i} = $em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($unIdVol);
            $etatPrecedent = $vol_{$i}->getPeriodeDeVol()->getEtat();
            if($etatPrecedent == "pendingCancel"){
                $vol_{$i}->getPeriodeDeVol()->setDeleste(true);
                $vol_{$i}->getPeriodeDeVol()->setEtat('cancel');
            }else{
                $vol_{$i}->getPeriodeDeVol()->setEtat('send');
            }

            $vol_{$i}->setModificationPonctuelle(0);

            $volhistorique_{$i} = $vol_{$i}->getVolHistoriqueParentable();
            $etatPrecedentHistorique = $volhistorique_{$i}->getPeriodeDeVol()->getEtat();
            if($etatPrecedentHistorique == "pendingCancel"){
                $volhistorique_{$i}->getPeriodeDeVol()->setDeleste(true);
                $volhistorique_{$i}->getPeriodeDeVol()->setEtat('cancel');
            }else{
                $volhistorique_{$i}->getPeriodeDeVol()->setEtat('send');
            }

            $i++;
        }

        //on enregistre les modifications dans la BDD
        $em->flush();

        return new Response(
            json_encode("[]")
        );
    }

    /**
     * Expedier messages SITA
     * @Security("has_role('ROLE_ADMIN')")
     *
     */
    public function expedierAction(Request $request)
    {

        //récupération du chemin d'enregistrement
        $chemin_enregistrementfichiermessage_ASMSSM = $this->container->getParameter('chemin_enregistrementfichiermessage_asmssm');
        $chemin_enregistrementfichiermessage_SCR = $this->container->getParameter('chemin_enregistrementfichiermessage_scr');

        //récupération des variables
        $aIdVolMessageAAcquitter = explode ("_",$request->get('idsVolCheckedString'));
        $aIdsVolMessagesASupprimer = explode ("_",$request->get('idsVolNotCheckedString'));
        $amodifsdestinataires = $request->get('amodifsdestinataires');

        //récupération des variables de sessions
        $session = $request->getSession();
        $aALTEAmessages = $session->get('vm_aalteamessages');

        //récupération de l'entity manager
        $em = $this->getDoctrine()->getManager();

        //récupération de la valeur de l'increment courant de la messagerie SITA
        $path = __DIR__.'/../Resources/config/parametres.yml';
        $myarraystring = file_get_contents($path);
        $myParametresArray = json_decode(str_replace("'","",$myarraystring));
        $incrementSITA = intval($myParametresArray->incrementmessageriesita);

        //récupération de l'email ALTEA et de l'incrément courant de la messagerie SITA
        $pathParam = getcwd().'/../src/AirCorsica/XKPlanBundle/Resources/config/parametres_globaux.yml';
        $parametres_globaux = Yaml::parse(file_get_contents($pathParam));
        foreach($parametres_globaux as $i => $parametre)
        {
            $messagereplyaddress = $parametre['messagereplyaddress'];
            $emailsitaaltea = $parametre['emailsitaaltea'];
        }

        //supression des vols non séléctionnés pour l'expédition des messages de la variable de session
        //---------------------------------------------------------------------------------------------
        if(count($aIdsVolMessagesASupprimer)!=0) {
            foreach ($aIdsVolMessagesASupprimer as $unIdVolNotChecked) {
                unset($aALTEAmessages[$unIdVolNotChecked]);
            }
        }

        //Supression des destinataires et ajouts de ceux créer pour les vols selectionnés de la variable de session
        //
        // TODO: a modifier pour gérer les emails, ici le test n'est que sur l'adresse SITA
        //----------------------------------------------------------------------------------------------------------
        if($amodifsdestinataires!=null) {
            foreach ($amodifsdestinataires as $keyVol => $unVol) {
                foreach ($unVol as $keyTypeMsg => $unTypeMsg) {
                    foreach ($unTypeMsg as $keyMsg => $unMsg) {
                        foreach ($unMsg as $keyAct => $uneAction) {
                            foreach ($uneAction as $keyAddr => $uneAddresse) {
                                if ($keyAct == "aAdressesSITAaSupprimer") {//supression adresse SITA
                                    /** @var ALTEAMessage $aALTEAmessages [$keyVol][$keyTypeMsg][$keyMsg] */
                                    $aALTEAmessages[$keyVol][$keyTypeMsg][$keyMsg]->removeUnDestinataire($uneAddresse);
                                } else {//ajout adresse SITA
                                    $aALTEAmessages[$keyVol][$keyTypeMsg][$keyMsg]->addUnDestinataire($uneAddresse);
                                }
                            }
                        }
                    }
                }
            }
        }


        //On  récupére uniquement les emails dans un tableau (pas la peine d'enlever ces enregistrement la classe ALTEAMessage les enlève des destinataire)
        //-------------------------------------------------------------------------------------------------------------------------------------------------
        foreach($aALTEAmessages as $keyVol=>$unVol){
            foreach($unVol as $keyTypeMsg=>$unTypeMsg){
                foreach($unTypeMsg as $keyMsg=>$unMsg){
                    if($keyTypeMsg == "ASMSSM"){//envoi de email SITA uniquement a des destinataires de messages ASM/SSM
                        $adestEmail = array();
                        foreach($unMsg->getAdestinataires() as $keyDest=>$unDest){
                            if($unDest['email']!=""){//recupére les destinataire sauf l'info de ASM SSM BASE (seul a avoir un email et une adresse)
                                $aunDestTemp = array("email" => $unDest['email'], "adresse" => $unDest['adresse'], "libelle" => $unDest['libelle'], "coordinateur" => $unDest['coordinateur']);
                                array_push( $adestEmail,$aunDestTemp);
                            }
                        }
                        if(count($adestEmail)!=0){
                            $unMsg->setAemailsdestinataires($adestEmail);
                        }
                    }
                }
            }
        }


        //les traitements sont terminés on met à jour la variable de session
        $session->set('vm_aalteamessages',$aALTEAmessages);

        //Rendu et envoi des messages ALTEA
        //----------------------------------
        foreach($aALTEAmessages as $keyVol=>$unVol){
            foreach($unVol as $keyTypeMsg=>$unTypeMsg){
                foreach($unTypeMsg as $keyMsg=>$unMsg){
                    /** @var ALTEAMessage $aALTEAmessages[$keyVol][$keyTypeMsg][$keyMsg] */

                    //on set la valeur de l'incrément courant de la messagerie SITA
                    $aALTEAmessages[$keyVol][$keyTypeMsg][$keyMsg]->setIncrementnumeromessagerieSITA($incrementSITA);

                    //rendu du contenu du message
                    $contenuALTEAmessage = $aALTEAmessages[$keyVol][$keyTypeMsg][$keyMsg]->render();

                    //generation du nom de fichier pour ce message
                    $nomfichierALTEAmessage = $aALTEAmessages[$keyVol][$keyTypeMsg][$keyMsg]->getFileName();

                    //-------------------------------------------------------------
                    // copie local de backup du message enregistrer sur le serveur
                    //-------------------------------------------------------------

                    if($keyTypeMsg == "ASMSSM"){
                        if($unMsg->didExistAlteaDestinatairesAvecAdresseSITA() == true) {//est-ce que les message ASM/SSM existant ne sont pas tous de type email
                            //Enregistrement du message sur le serveur dans le répertoire /src/AirCorsica/XKPlanBundle/Resources/public/temporaire
                            if ($this->container->get('kernel')->getEnvironment() == "prod") {
                                file_put_contents($chemin_enregistrementfichiermessage_ASMSSM . $nomfichierALTEAmessage, $contenuALTEAmessage);
                                file_put_contents(getcwd() . $chemin_enregistrementfichiermessage_SCR . $nomfichierALTEAmessage, $contenuALTEAmessage);
                            } else {
//                            file_put_contents($chemin_enregistrementfichiermessage_ASMSSM.$nomfichierALTEAmessage,$contenuALTEAmessage);
                                file_put_contents(getcwd() . $chemin_enregistrementfichiermessage_SCR . $nomfichierALTEAmessage, $contenuALTEAmessage);
                            }
                        }

                    }
                    if($keyTypeMsg == "SCR"){
                        //Enregistrement du message sur le serveur dans le répertoire /src/AirCorsica/XKPlanBundle/Resources/public/temporaire
                        //file_put_contents(getcwd().$chemin_enregistrementfichiermessage_SCR.$nomfichierALTEAmessage,$contenuALTEAmessage);
                        //corection suite coup de fil à thierry suite au mail d'OLivia du 23/02/2018
                        if($this->container->get( 'kernel' )->getEnvironment() == "prod") {
//                            file_put_contents($chemin_enregistrementfichiermessage_ASMSSM.$nomfichierALTEAmessage,$contenuALTEAmessage);
                            file_put_contents(getcwd().$chemin_enregistrementfichiermessage_SCR.$nomfichierALTEAmessage,$contenuALTEAmessage);
                        }else{
//                            file_put_contents($chemin_enregistrementfichiermessage_ASMSSM.$nomfichierALTEAmessage,$contenuALTEAmessage);
                            file_put_contents(getcwd().$chemin_enregistrementfichiermessage_SCR.$nomfichierALTEAmessage,$contenuALTEAmessage);
                        }
                    }

                    //---------------------------------------
                    // envoie du message à une adresse email
                    //---------------------------------------

                    //on récupére la date courante
                    $now = new \DateTime();

                    //on récupére le corps du message
                    $aSsim = array();
                    $aSsim['txt'] = $contenuALTEAmessage;



                    //on envoie l'email pour les SCR sur Altea
                    //----------------------------------------
                    if($keyTypeMsg != "ASMSSM")
                    {
                        //on récupére le chemin du fichier
                        $aAttachement['txt'] = getcwd().$chemin_enregistrementfichiermessage_SCR.$nomfichierALTEAmessage;

                        if($this->container->get( 'kernel' )->getEnvironment() == "prod") {
                            $this->sendEmail($keyTypeMsg . " du " . $now->format("d/m/Y h:i:s") . " file:" . $nomfichierALTEAmessage, $messagereplyaddress, $emailsitaaltea, "AirCorsicaXKPlanBundle:Emails:asmssmscr.html.twig", $aSsim, $aAttachement);
                        }else{
                            $this->sendEmail($keyTypeMsg . " du " . $now->format("d/m/Y h:i:s") . " file:" . $nomfichierALTEAmessage, $messagereplyaddress, "damien.cayzac@sitec.fr", "AirCorsicaXKPlanBundle:Emails:asmssmscr.html.twig", $aSsim, $aAttachement);
                        }
                    }

                    //on envoi les emails au destinataires email des ASM/SSM (nouvelle fonctionnalité)
                    //--------------------------------------------------------------------------------

                    //on récupére le corps du message
                    $aSsim = array();
                    $aSsim['txt'] = $unMsg->renderForEmail();
                    $aAttachement = array();

                    if($keyTypeMsg == "ASMSSM") {
                        $aDestinatairesEmailASMSSM = $unMsg->getAemailsdestinataires();
                        if (count($aDestinatairesEmailASMSSM) != 0) {

                            //pour chaque destinataire d'un message email
                            foreach ($aDestinatairesEmailASMSSM as $unDestDuMsg) {
                                if ($unDestDuMsg['adresse'] != "MUCSCXK") { //empéche d'envoyer une copie a ALTEA (seul destinataire ayant une adresseSITA et un emailSITA) à voir avec eux
                                    if ($this->container->get('kernel')->getEnvironment() == "prod") {
                                        $this->sendEmail($keyTypeMsg . " du " . $now->format("d/m/Y h:i:s"), $messagereplyaddress, $unDestDuMsg['email'], "AirCorsicaXKPlanBundle:Emails:asmssmscr.html.twig", $aSsim);
                                    } else {
                                        $this->sendEmail($keyTypeMsg . " du " . $now->format("d/m/Y h:i:s"), $messagereplyaddress, $unDestDuMsg['email'], "AirCorsicaXKPlanBundle:Emails:asmssmscr.html.twig", $aSsim);
                                    }
                                }

                            }
                        }
                    }

                    /*
                                        //pour chaque destinataire ce ce message
                                        foreach($aDestinatairesSITADeCeMessage as $unDestDuMsg){

                                            //on récupére l'email dans l'objet destinataire SITA
                                            $emailcourant = $unDestDuMsg->getEmail();

                                            //on récupére le corps du message
                                            $aSsim = array();
                                            $aSsim['txt'] = $contenuALTEAmessage;

                                            //on récupére le chemin du fichier
                                            $aAttachement['txt'] = getcwd().$chemin_enregistrementfichiermessage.$nomfichierALTEAmessage;

                                            //TODO pas de test si le mail existe
                                            //TODO remplacer l'email de verification damien.cayzac@sitec.fr par la bonne
                                            //TODO effacer la ligne suivante (seulement email de test on ne prend pas ceux de la base)
                                            $emailcourant = "xkplan@aircorsica.fr";

                                            //on envoi le mail
                                            $this->sendEmail($keyTypeMsg." du ".$now->format("d/m/Y h:i:s")." file:".$nomfichierALTEAmessage,$emailcourant,"damien.cayzac@sitec.fr","AirCorsicaXKPlanBundle:Emails:asmssmscr.html.twig",$aSsim,$aAttachement);

                                            //TODO enlever le break qui n'envoi qu'un email de test pour la démo pour chaque message
                                            break 1;
                                        }*/

                }

            }
        }

        //TODO lors du déploiement enregistrer l'increment courant de l'application XKPLAN de Xavier dans le fichier de conf pour qu'ils reparte avec le bon numéro
        //mise à jour de la variable d'incrémentation de la messagerie SITA et on l'enregistre dans le fichier de conf
        //Attention le fichier parametres.yml doit avoir les droits en écriture sinon erreur
        $incrementSITA++;
        $myParametresJson = "{\"incrementmessageriesita\":".$incrementSITA."}";
        $dumper = new Dumper();
        $yaml = $dumper->dump($myParametresJson);
        file_put_contents($path, $yaml);

        //acquitter les vols selectionnés dont ont vient d'expédier les messages
        //----------------------------------------------------------------------
        $i=0;
        foreach( $aIdVolMessageAAcquitter as $unIdVol ) {
            //Modification du vol d'origine avec une
            //nouvelle période de Vol: datedébut identique et datefin le jour précédent le jour transmis
            //------------------------------------------------------------------------------------------
            $vol_{$i} = $em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($unIdVol);
            $etatPrecedent = $vol_{$i}->getPeriodeDeVol()->getEtat();
            if($etatPrecedent == "pendingCancel"){
                $vol_{$i}->getPeriodeDeVol()->setDeleste(true);
                $vol_{$i}->getPeriodeDeVol()->setEtat('cancel');
            }else{
                $vol_{$i}->getPeriodeDeVol()->setEtat('send');
            }

            $vol_{$i}->setModificationPonctuelle(0);

            $volhistorique_{$i} = $vol_{$i}->getVolHistoriqueParentable();
            $etatPrecedentHistorique = $volhistorique_{$i}->getPeriodeDeVol()->getEtat();
            if($etatPrecedentHistorique == "pendingCancel"){
                $volhistorique_{$i}->getPeriodeDeVol()->setDeleste(true);
                $volhistorique_{$i}->getPeriodeDeVol()->setEtat('cancel');
            }else{
                $volhistorique_{$i}->getPeriodeDeVol()->setEtat('send');
            }

            $i++;
        }
        //on enregistre les modifications dans la BDD
        $em->flush();

        //retour methode AJAX
        return new Response(
            json_encode("[]")
        );
    }

    /**
     * modifier le message texte ASM/SSM/SCR encapsulé dans le coprs d'un des message ALTEA en session
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function majpartietextemessagealteaAction(Request $request)
    {
        $idVol = $request->get('idVol');
        $typeMessage = $request->get('typeMessage');
        if($typeMessage!="SCR"){$typeMessage="ASMSSM";}
        $idMessageALTEA = $request->get('idALTEAMessage');
        $texteMessageCorps = $request->get('texteMessageCorps');

        //récupération des variables de sessions
        $session = $request->getSession();
        $aALTEAmessages = $session->get('vm_aalteamessages');

        /** @var ALTEAMessage $unMessageALTEA */
        foreach ($aALTEAmessages[$idVol][$typeMessage] as $unMessageALTEA) {
            //on cherche le message à éditer
            if($unMessageALTEA->getIdalteamessage() == $idMessageALTEA){
                $unMessageALTEA->setMessagetexte($texteMessageCorps);
                break;
            }
        }

        //le traitement est terminé on met à jour la variable de session
        $session->set('vm_aalteamessages',$aALTEAmessages);


        return new Response(
            json_encode("[]")
        );
    }

//    /**
//     * modifier les destinataires d'un message ASM/SSM/SCR encapsulé dans l'en tête d'un des message ALTEA en session
//     * @Security("has_role('ROLE_ADMIN')")
//     */
//    public function majdestinatairesmessagealteaAction(Request $request)
//    {
//        $idVol = $request->get('idVol');
//        $typeMessage = $request->get('typeMessage');
//        $aAdressesSITANonValides = explode(',',$request->get('adressesSITANonValide'));
//        $aNouvellesAdressesSITACrees = explode(',',$request->get('nouvellesAdressesSITACrees'));// ADR5XJ&BASE&monLibelle&ALLEMAGNE,LLP5C4&BASE&monlibelle2&ITALIE ...etc
//        $idMessageALTEA = $request->get('idALTEAMessage');
//
//        //récupération des variables de sessions
//        $session = $request->getSession();
//        $aALTEAmessages = $session->get('vm_aalteamessages');
//
//        /** @var ALTEAMessage $unMessageALTEA */
//        foreach ($aALTEAmessages[$idVol][$typeMessage] as $unMessageALTEA) {
//            //on cherche le message à éditer
//            if($unMessageALTEA->getIdalteamessage() == $idMessageALTEA){
//                //on parcour les destinataires du message pour enlever ceux décochés
//                foreach ($aAdressesSITANonValides as $uneAdresseSITAASUpprimer){
//                    $unMessageALTEA->removeUnDestinataire($uneAdresseSITAASUpprimer);
//                }
//                //on parcour les destinataires du message pour rajouter ceux qui viennent d'être crées
//                foreach ($aNouvellesAdressesSITACrees as $uneAdresseSITAAAjouter){
//                    $aElementsUneAdresseSITAAAjouter = explode('&',$uneAdresseSITAAAjouter);
//                    $unMessageALTEA->addUnDestinataire($aElementsUneAdresseSITAAAjouter[0],$aElementsUneAdresseSITAAAjouter[1],$aElementsUneAdresseSITAAAjouter[2],$aElementsUneAdresseSITAAAjouter[3]);
//                }
//                break;
//            }
//        }
//
//        //le traitement est terminé on met à jour la variable de session
//        $session->set('vm_aalteamessages',$aALTEAmessages);
//
//        return new Response(
//            json_encode("[]")
//        );
//    }


    /**
     * * Messagerie SITA
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function messagerieAction(Request $request)
    {
        $aIdVolMessageAGenerer = explode ("_",$request->get('arrayIdVolsPourGenererSCRASMSSM'));
        $generer_asmssm = $request->get('asm_ssm');
        $generer_scr = $request->get('scr');
        $generer_ssim = $request->get('ssim');
        $generer_amos = $request->get('amos');

        $em = $this->getDoctrine()->getManager();

        //récupération des variables de sessions
        $session = $request->getSession();
        if($session->has('vm_aalteamessages')) {//si l'attribut vm_aalteamessages existe dans la variable de session, on l'efface
            $session->remove('vm_aalteamessages');
        }

        $amessages = array();
        $avols = array();

        if( ($generer_asmssm == "on") || ($generer_scr == "on") ) {

            $i = 0;
            foreach ($aIdVolMessageAGenerer as $unIdVol) {
                $unVolTemp = $em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($unIdVol);
                $avols[$i]['id'] = $unVolTemp->getId();
                $avols[$i]['numerovol'] = $unVolTemp->getNumero();
                $avols[$i]['ligne'] = $unVolTemp->getLigne()->getAeroportDepart()->getCodeIATA() . '-' . $unVolTemp->getLigne()->getAeroportArrivee()->getCodeIATA();
                $avols[$i]['dates'] = $unVolTemp->getPeriodeDeVol()->getDateDebut()->format('d/m/Y') . '-' . $unVolTemp->getPeriodeDeVol()->getDateFin()->format('d/m/Y');
                $avols[$i]['heures'] = $unVolTemp->getPeriodeDeVol()->getDecollage()->format('H:i') . '-' . $unVolTemp->getPeriodeDeVol()->getAtterissage()->format('H:i');
                $avols[$i]['type'] = $unVolTemp->getTypeDeVol()->getCodeService();
                $i++;
            }


            //++++++++++++++++++++++++++++++++++++++++++++
            //    GENERATION DES MESSAGE ASM/SSM et SCR
            //++++++++++++++++++++++++++++++++++++++++++++

            $amessages = array();

            // Pour chaque vol selectionné
            foreach ($aIdVolMessageAGenerer as $unIdVol) {
                $unVolTemp = $em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($unIdVol);

                //++++++++++++++++++++
                //  analyse commune
                //++++++++++++++++++++

                $datamessagetogenerate = $unVolTemp->isVolNecessiteMessages();

                // Si il y a des messages à généreer
                if ($datamessagetogenerate != false) {

                    if ($generer_scr == "on") {

                        if (isset($datamessagetogenerate['SCR'])) { //ce vol necessite bien l'envoi d'un ou plusieurs message SCR

                            $amessages[$unVolTemp->getId()]['SCR'] = array();

                            //+++++++++++++++++++++++++++++++++
                            //    GENERATION DE MESSAGE SCR
                            //+++++++++++++++++++++++++++++++++

                            $messagesEnvoyesSurALTEA = true;
                            $monSCR = new SCR($unVolTemp, $this->getDoctrine()->getManager(), $this->container);

                            $lotsDeMessages = array();
                            $lotsDeMessages = $monSCR->generer($datamessagetogenerate);

                            //récupération de la valeur de l'increment courant de la messagerie SITA
                            $path = __DIR__.'/../Resources/config/parametres.yml';
                            $myarraystring = file_get_contents($path);
                            $myParametresArray = json_decode(str_replace("'","",$myarraystring));
                            $incrementSITA = intval($myParametresArray->incrementmessageriesita);

                            foreach ($lotsDeMessages as $unmessage) {

                                $textmessage = $unmessage->render();
                                $messagelibelle = $unmessage->getLibelle();
                                $adestinataires = $unmessage->getDestinataires();
                                $identifiantUnique = md5(microtime());

                                $monALTEAMessage = new ALTEAMessage($identifiantUnique,$i, $incrementSITA/*$this->container->getParameter('numeromessageriesita')*/, $unVolTemp->getId(), $this->container->getParameter('codeemetteursita'), $adestinataires, $textmessage, $messagelibelle);

                                //array_push($amessages[$unVolTemp->getId()]['SCR'], $monALTEAMessage);
                                $amessages[$unVolTemp->getId()]['SCR'][$identifiantUnique] = $monALTEAMessage;

//                                $contenuALTEAmessage = $monALTEAMessage->render();
//                                $nomfichierALTEAmessage = $monALTEAMessage->getFileName();
//
//                                //Enregistrement du message sur le serveur dans le répertoire /src/AirCorsica/XKPlanBundle/Resources/public/temporaire
//                                //TODO à remplacer par une adresse SMTP
//                                file_put_contents(getcwd().$chemin_enregistrementfichiermessage.$nomfichierALTEAmessage,$contenuALTEAmessage);

                                $i++;
                            }

                        }

                    }

                    if ($generer_asmssm == "on") {

                        if ((isset($datamessagetogenerate['ASM'])) || (isset($datamessagetogenerate['SSM']))) { //ce vol necessite bien l'envoi d'un message SSM ou de plusieurs message ASM

                            $amessages[$unVolTemp->getId()]['ASMSSM'] = array();

                            //+++++++++++++++++++++++++++++++++++++
                            //    GENERATION DE MESSAGE ASM/SSM
                            //+++++++++++++++++++++++++++++++++++++

                            $messagesEnvoyesSurALTEA = true;
                            $monASMSSM = new ASMSSM($this->getDoctrine()->getManager(), $this->container);

                            $lotsDeMessages = array();
                            $lotsDeMessages = $monASMSSM->generer($datamessagetogenerate);

                            //récupération de la valeur de l'increment courant de la messagerie SITA
                            $path = __DIR__.'/../Resources/config/parametres.yml';
                            $myarraystring = file_get_contents($path);
                            $myParametresArray = json_decode(str_replace("'","",$myarraystring));
                            $incrementSITA = intval($myParametresArray->incrementmessageriesita);

                            foreach ($lotsDeMessages as $unmessage) {

                                $textmessage = $unmessage->render();
                                $messagelibelle = $unmessage->getLibelle();
                                $adestinataires = $unmessage->getDestinataires();
                                $identifiantUnique = md5(microtime());

                                $monALTEAMessage = new ALTEAMessage($identifiantUnique,$i, $incrementSITA/*$this->container->getParameter('numeromessageriesita')*/, $unVolTemp->getId(), $this->container->getParameter('codeemetteursita'), $adestinataires, $textmessage, $messagelibelle);

                                //array_push($amessages[$unVolTemp->getId()]['ASMSSM'], $monALTEAMessage);
                                $amessages[$unVolTemp->getId()]['ASMSSM'][$identifiantUnique] = $monALTEAMessage;

//                                $contenuALTEAmessage = $monALTEAMessage->render();
//                                $nomfichierALTEAmessage = $monALTEAMessage->getFileName();
//
//                                //Enregistrement du message sur le serveur dans le répertoire /src/AirCorsica/XKPlanBundle/Resources/public/temporaire
//                                //TODO à remplacer par une adresse SMTP
//                                file_put_contents(getcwd().$chemin_enregistrementfichiermessage.$nomfichierALTEAmessage,$contenuALTEAmessage);

                                $i++;
                            }

                        }

                    }


                }
            }

        }

        //on enregistre la liste de messages ALTEA en session
        $session->set('vm_aalteamessages',$amessages);

        //on récupére les filtres
        $paramsFilters = $session->get('vm_volsfilter');

        $amos_list =null;
        if($generer_amos == "on"){
            ini_set('max_execution_time', 0);
            $amosManager = $this->get('air_corsica_xk_plan.amos_manager');
            $amosManager->generateXml($request->request->get("date_debut"), $request->request->get("date_fin"));
            $path = $_SERVER['DOCUMENT_ROOT'].'/AMOS/';
            $amos_list = $amosManager->getListeXmlAmos($path, 10);
        }


        //$ssim = $request->get('ssim');
        $aSsim = array();
        //if($ssim == "on"){
        if($generer_ssim == "on"){

            //récupération de l'email ALTEA et de l'incrément courant de la messagerie SITA
            $pathParam = getcwd().'/../src/AirCorsica/XKPlanBundle/Resources/config/parametres_globaux.yml';
            $parametres_globaux = Yaml::parse(file_get_contents($pathParam));
            foreach($parametres_globaux as $i => $parametre)
            {
                $messagereplyaddress = $parametre['messagereplyaddress'];
                $emailsitaaltea = $parametre['emailsitassim7'];
            }

            //Uniquement pour ce script
            ini_set('max_execution_time', 0);
            $res = $this->generateSSIM($request);
            $csv = $this->genererCSV($res);
            $txt = $this->genererTXT($res);
            $aSsim = array();
            $aSsim['textBrut'] = $res;
            $aSsim['csv'] = $csv;
            $aSsim['txt'] = $txt;
            $aAttachement['txt'] = $_SERVER['DOCUMENT_ROOT'].'/SSIM/'.$aSsim['txt'];
            $now = new \DateTime();
            if($this->container->get( 'kernel' )->getEnvironment() == "prod"){
                $this->sendEmail("SSIM7 du ".$now->format("d/m/Y h:i:s"),$messagereplyaddress,$emailsitaaltea,"AirCorsicaXKPlanBundle:Emails:ssim.html.twig",$aSsim,$aAttachement);
            }else{
                $this->sendEmail("SSIM7 du ".$now->format("d/m/Y h:i:s"),"xkplan@aircorsica.fr","damien.cayzac@sitec.fr","AirCorsicaXKPlanBundle:Emails:ssim.html.twig",$aSsim,$aAttachement);
            }


        }
        if(sizeof($aSsim) || $amos_list){
            return $this->render('AirCorsicaXKPlanBundle:Vol:messagerie.html.twig', array( 'paramsListeVolsFilter' => $paramsFilters, 'aVols' => $avols, 'aMessages' => $amessages, 'ssim' => $aSsim, 'amos_path_file' => $amos_list));
        }
        return $this->render('AirCorsicaXKPlanBundle:Vol:messagerie.html.twig', array(
            'paramsListeVolsFilter' => $paramsFilters, 'aVols' => $avols, 'aMessages' => $amessages,
        ));
    }

    private function generateSSIM(Request $request){
        $em = $this->getDoctrine()->getManager();
        /** @var VolRepository $repoVol */
        $repoVol = $em->getRepository('AirCorsicaXKPlanBundle:Vol');
        $orderParPeriode = "ASC";

        $repoTemplate = $em->getRepository('AirCorsicaXKPlanBundle:Template');
        $templateCurrent = $repoTemplate->find($this->get('session')->get('template'));

        $vols = $repoVol->getVolsFilter($request,$templateCurrent,$orderParPeriode);

        $SSIM = new SSIM($vols);

        $return = $SSIM->genererMessage($em,$request->get('date_debut'),$request->get('date_fin'));

        return $return;
    }

    private function genererCSV($string){
        $now = new \DateTime();
        $filename = 'ssim_'.$now->format("dmY_His").'.csv';
        $directory = $_SERVER['DOCUMENT_ROOT'].'/SSIM/';
        $fp = fopen($directory.$filename, 'w+');

        $aReturn = explode("\n",$string);
        foreach ($aReturn as $ligneCsv){
            $ligneCsv = str_replace(" ","#",$ligneCsv);
            $csv = wordwrap($ligneCsv,1,";",true);
            $csv = str_replace('#'," ",$csv);
            $aCSV = explode(";",$csv);
            fputcsv($fp, $aCSV,";");
        }
        fclose($fp);

        return $filename;
    }

    private function genererTXT($string){
        $now = new \DateTime();
        $filename = 'ssim_'.$now->format("dmY_His").'.txt';
        $directory = $_SERVER['DOCUMENT_ROOT'].'/SSIM/';
        $fp = fopen ($directory.$filename, 'w+');
        fputs ($fp, $string);
        fclose($fp);

        return $filename;
    }

    /**
     * * Ajout destinataire messagerie SITA
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function ajoutDestinataireMessagerieAction(Request $request)
    {

        $idVol = $request->get('id_vol');
        $typeMessage = $request->get('type_message');
        $inctypeMessage = $request->get('inc_type_message');
        $nbrMessASMSSM = $request->get('nbr_mess_asmssm');

        $em = $this->getDoctrine()->getManager();

        $groupes_sita = $em->getRepository('AirCorsicaXKPlanBundle:GroupeSITA')->findAll();
        $adresses_sita = $em->getRepository('AirCorsicaXKPlanBundle:AdresseSITA')->findAll();

        $adresseSITum = new AdresseSITA();
        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\AdresseSITAType', $adresseSITum);
        $form->handleRequest($request);

        return $this->render('AirCorsicaXKPlanBundle:Vol:ajout_destinataire_messagerie_sita.html.twig', array(
            'groupes_sita' => $groupes_sita,
            'adresses_sita' => $adresses_sita,
            'adresseSITum' => $adresseSITum,
            'form' => $form->createView(),
            'request'       => $request,
            'id_vol' => $idVol,
            'type_message' => $typeMessage,
            'inc_type_message' => $inctypeMessage,
            'nbr_mess_asmssm' => $nbrMessASMSSM
        ));
    }

    /**
     * Creation d'un microtime unique pour popup ajout adresse supp
     * @Security("has_role('ROLE_ADMIN')")
     * @return JsonResponse
     */
    /*public function setnewmicrotimeAction(Request $request){

        $microtime_unique = md5(microtime());

        $response = new JsonResponse();

        $response->setData($microtime_unique);

        return $response;
    }*/



    /**
     * Creation d'une adresse SITA via la popu de la messagerie
     * @Security("has_role('ROLE_ADMIN')")
     * @return JsonResponse
     */
    public function setnewadressesitaAction(Request $request){
        $em = $this->getDoctrine()->getManager();

        $groupe_sita = $em->getRepository('AirCorsicaXKPlanBundle:GroupeSITA')->find($request->get('groupe_sita'));

        $adresseSITum = new AdresseSITA();

        $adresseSITum->setAdresseSITA($request->get('adresse_sita'));
        $adresseSITum->setLibelle($request->get('libelle_sita'));
        $adresseSITum->setEmail($request->get('email_sita'));
        $adresseSITum->setGroupeSITA($groupe_sita);

        $adresseSITum->setSuiviDemandeSlot(false);

        $em->persist($adresseSITum);
        $em->flush();

        $response = new JsonResponse();

        $response->setData(array(
            'valide' => true,
            'response' => "Adresse créé avec succès.",
            'id_groupe' => $adresseSITum->getGroupeSITA()->getId(),
            'ligne_tableau' => "<tr><td><input type = 'checkbox' 
                                    value='".$adresseSITum->getId()."' 
                                    data-addrsita = '".$adresseSITum->getAdresseSITA()."'
                                    data-emailsita = '".$adresseSITum->getEmail()."'
                                    data-addrlibellesita = '".$adresseSITum->getLibelle()."'
                                    data-addrgroupesita = '".$adresseSITum->getGroupeSITA()->getNom()."'
                                    data-addrcoordsita = '".$adresseSITum->getPaysCoordinateur()."'
                                class='id_adresse_sita' /> ".$adresseSITum->getAdresseSITA()." - ".$adresseSITum->getLibelle()."</td></tr>"
        ));

        return $response;
    }

    /**
     * * * Sidebar Messagerie SITA
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sidebarmessageAction(Request $request)
    {
        return $this->render(
            'AirCorsicaXKPlanBundle:Vol:sidebar_message.html.twig',
            array(
                'request'       => $request,
            )
        );
    }


    /**
     * update en masse vol entities.
     *
     */
    public function updatemasseAction(Request $request)
    {
        $referer = $request->headers->get('referer');
        $em = $this->getDoctrine()->getManager();
        /** @var VolRepository $repoVol */
        $repoVol = $em->getRepository('AirCorsicaXKPlanBundle:Vol');
        $aIdVols = explode('_',$request->get('ids_vol'));
            switch ($request->get('choix_ponctuel')) {
                case 'choix_avion':
                    foreach ($aIdVols as $idVol) {
                        $deleteVol = 0;
                        /** @var Vol $vol */
                        $vol = $repoVol->find($idVol);
                        /** @var Vol $volOrigine */
                        $volOrigine = clone $vol;
                        $periodeVol = $vol->getPeriodeDeVol();

                        $aJoursSemaine = explode("_",$request->get("jour_val"));

                        $aJourValidite = array();
                        foreach ($aJoursSemaine as $jour){
                            if($jour != "-"){
                                $aJourValidite[] = $jour;
                            }
                        }
                        $jourValiditeDuVol = $periodeVol->getJoursDeValidite();

                        $aJourAmodifer = array();
                        if(sizeof($aJourValidite)==0){
                            $aJourAmodifer = $jourValiditeDuVol;
                        }else{
                            $compt =0;

                            foreach ($aJourValidite as $key=>$jour){
                                if(in_array($jour,$jourValiditeDuVol)){
                                    $compt ++;
                                    $aJourAmodifer[] = $jour;
                                }
                            }

                            if(sizeof($aJourAmodifer) == 0){
                                $request->getSession()->getFlashBag()->add('warning', 'Nous n\'avons pas trouvé de jour à modifier correspondant à vos filtres.');
                                return $this->redirect($_SERVER['HTTP_REFERER'],'302');
                            }
                        }

                        $repoAvion = $em->getRepository('AirCorsicaXKPlanBundle:Avion');
                        $avion = $repoAvion->find($request->get('avion_ponctuel'));
                        $vol->setAvion($avion);
                        $vol->setCompagnie($avion->getCompagnie());

                        $typeDifferent = 0;
                        $changementHeure = 0;

                        if($request->get('planning') && $request->get('heureminute_depart') && $request->get('heureminute_arrivee'))
                        {
                            $changementHeure = 1;
                            if($avion->getTypeAvion()->getId() != $volOrigine->getAvion()->getTypeAvion()->getId()){
                                $typeDifferent = 1;
                            }
                            $heureMinuteDepart = $request->get("heureminute_depart");
                            $heureMinuteArrivee = $request->get("heureminute_arrivee");

                            $aHeureMinuteDepart = explode(":",$heureMinuteDepart);
                            if(sizeof($aHeureMinuteDepart)<2){
                                $aHeureMinuteDepart[0] = "00";
                                $aHeureMinuteDepart[1] = "00";
                            }
                            $aHeureMinuteArrivee = explode(":",$heureMinuteArrivee);
                            if(sizeof($aHeureMinuteArrivee)<2){
                                $aHeureMinuteArrivee[0] = "00";
                                $aHeureMinuteArrivee[1] = "00";
                            }


                            if(($aHeureMinuteDepart[0] == "00" && $aHeureMinuteDepart[1] == "00") &&
                                ($aHeureMinuteArrivee[0] != "00" || $aHeureMinuteArrivee[0] != "00")){
                                $nouveauTempsDeVol = $this->ajaxGetTempdsDeVolAction($volOrigine->getLigne()->getAeroportDepart(),$volOrigine->getLigne()->getAeroportArrivee(),$avion,$volOrigine->getPeriodeDeVol()->getDateDebut(),$volOrigine->getPeriodeDeVol()->getDateFin());
                                $nouveauTempsDeVol = substr(explode(":",$nouveauTempsDeVol->getContent())[1],0,-1);
                                if($nouveauTempsDeVol == "null"){
                                    //Valeur par défaut
                                    $nouveauTempsDeVol = "59";
//                                $request->getSession()->getFlashBag()->add('warning', 'Une erreure s\'est produite. Aucun temps de vol trouver. Veuillez vérifier qu\'il existe bien un temps de vol pour le vol et l\'avion sélectionné.');
//                                return $this->redirect($_SERVER['HTTP_REFERER'],'302');
                                }
                                $arrivee = clone $volOrigine->getPeriodeDeVol()->getAtterissage();
                                $arrivee->setTime($aHeureMinuteArrivee[0],$aHeureMinuteArrivee[1]);
                                $depart = $arrivee;
                                $depart->sub(new \DateInterval("PT".$nouveauTempsDeVol."M"));

//                            $depart = clone $arrivee;
//                            $vol->getPeriodeDeVol()->setDecollage($arrivee);
                            }else {
                                /** @var \DateTime $depart */
                                $depart = clone $volOrigine->getPeriodeDeVol()->getDecollage();
                                $depart->setTime($aHeureMinuteDepart[0], $aHeureMinuteDepart[1]);
                            }

                            if(($aHeureMinuteDepart[0] != "00" || $aHeureMinuteDepart[1] != "00") &&
                                ($aHeureMinuteArrivee[0] == "00" && $aHeureMinuteArrivee[1] == "00")){
                                $nouveauTempsDeVol = $this->ajaxGetTempdsDeVolAction($volOrigine->getLigne()->getAeroportDepart(),$volOrigine->getLigne()->getAeroportArrivee(),$avion,$volOrigine->getPeriodeDeVol()->getDateDebut(),$volOrigine->getPeriodeDeVol()->getDateFin());
                                $nouveauTempsDeVol = substr(explode(":",$nouveauTempsDeVol->getContent())[1],0,-1);
                                if($nouveauTempsDeVol == "null"){
                                    $nouveauTempsDeVol = "59";
//                                $request->getSession()->getFlashBag()->add('warning', 'Une erreure s\'est produite. Aucun temps de vol trouver. Veuillez vérifier qu\'il existe bien un temps de vol pour le vol et l\'avion sélectionné.');
//                                return $this->redirect($_SERVER['HTTP_REFERER'],'302');
                                }
                                $depart = clone $volOrigine->getPeriodeDeVol()->getDecollage();
                                $depart->setTime($aHeureMinuteDepart[0], $aHeureMinuteDepart[1]);
                                $arrivee = clone $depart;
                                $arrivee->add(new \DateInterval("PT".$nouveauTempsDeVol."M"));

//                            $arrivee = clone  $depart;
//                            $vol->getPeriodeDeVol()->setAtterissage($depart);
                            }else{
                                /** @var \DateTime $arrivee */
                                $arrivee = clone $volOrigine->getPeriodeDeVol()->getAtterissage();
                                $arrivee->setTime($aHeureMinuteArrivee[0],$aHeureMinuteArrivee[1]);
                            }

                            $periodeVol->setDecollage($depart);
                            $periodeVol->setAtterissage($arrivee);
                            $vol->setPeriodeDeVol($periodeVol);
                        }elseif($avion->getTypeAvion()->getId() != $volOrigine->getAvion()->getTypeAvion()->getId()){

                            //$tempsDeVolOrigine = $this->ajaxGetTempdsDeVolAction($volOrigine->getLigne()->getAeroportDepart(),$volOrigine->getLigne()->getAeroportArrivee(),$volOrigine->getAvion(),$volOrigine->getPeriodeDeVol()->getDateDebut(),$volOrigine->getPeriodeDeVol()->getDateFin());
                            $nouveauTempsDeVol = $this->ajaxGetTempdsDeVolAction($volOrigine->getLigne()->getAeroportDepart(),$volOrigine->getLigne()->getAeroportArrivee(),$avion,$volOrigine->getPeriodeDeVol()->getDateDebut(),$volOrigine->getPeriodeDeVol()->getDateFin());
                            $nouveauTempsDeVol = substr(explode(":",$nouveauTempsDeVol->getContent())[1],0,-1);
                            if($nouveauTempsDeVol == "null"){
                                //Valeur par défaut
                                $nouveauTempsDeVol = "59";
//                                $request->getSession()->getFlashBag()->add('warning', 'Une erreure s\'est produite. Aucun temps de vol trouver. Veuillez vérifier qu\'il existe bien un temps de vol pour le vol et l\'avion sélectionné.');
//                                return $this->redirect($_SERVER['HTTP_REFERER'],'302');
                            }
                            $depart = clone $volOrigine->getPeriodeDeVol()->getDecollage();
                            $depart->add(new \DateInterval("PT".$nouveauTempsDeVol."M"));
                            $vol->getPeriodeDeVol()->setAtterissage($depart);
                            $typeDifferent = 1;
                        }

                        if($avion->getCompagnie()->getId() != $volOrigine->getAvion()->getCompagnie()->getId()){
                            $typeDifferent = 1;
                        }

                        $debutFilre = $request->get('debut_filtre');
                        if($debutFilre != ""){
                            $debut = new \DateTime($debutFilre);

                            if($debut<$periodeVol->getDateDebut()){
                                $debut = $periodeVol->getDateDebut();
                            }
                        }else{
                            $debut = $periodeVol->getDateDebut();
                        }

                        $finFilre = $request->get('fin_filtre');
                        if($finFilre != ""){
                            $fin = new \DateTime($finFilre);

                            if($fin>$periodeVol->getDateFin()){
                                $fin = $periodeVol->getDateFin();
                            }
                        }else{
                            $fin = $periodeVol->getDateFin();
                        }

                        $periodeVol->setDateDebut($debut);
                        $periodeVol->setDateFin($fin);

                        if($typeDifferent)
                        {
                            if("send" == $this->getEtatApresMaj($volOrigine,$vol)){
                                $periodeVol->setEtat("send");
                                $vol->setModificationPonctuelle(0);
                            }else{
                                $periodeVol->setEtat("pendingSend");
                                $volHistoriqueRoot = $volOrigine->getVolHistoriqueParentable()->getRoot();
                                if("send" == $volHistoriqueRoot->getPeriodeDeVol()->getEtat()) {
                                    $vol->setModificationPonctuelle(1);
                                }else{
                                    $vol->setModificationPonctuelle(0);
                                }
                            }
                        }elseif($changementHeure){
                            $periodeVol->setEtat($this->getEtatApresMaj($volOrigine,$vol));
                        }

                        $periodeVol->setJoursDeValidite($aJourAmodifer);
                        $vol->setPeriodeDeVol($periodeVol);

                        $vol->updateVolPonctuel($volOrigine,$em );

                        $periodeVol->corrigerJourDeValidite(true);
                        $periodeVol->corrigerDate(true);
                        $volHistoriqueRoot = $volOrigine->getVolHistoriqueParentable()->getRoot();
                        if("send" == $volHistoriqueRoot->getPeriodeDeVol()->getEtat()){
                            $volHistoriqueNew = VolHistorique::createFromVol($vol,null, $this->container->get('security.token_storage')->getToken()->getUser());
                            Vol::saveVolInHistorique($volHistoriqueNew,$em,$volOrigine->getVolHistoriqueParentable());
                            $vol->setVolHistoriqueParentable($volHistoriqueNew);
                            $volHistoriqueNew->setVolHistorique($vol);
                        }else{
                            $volHistoriqueNew = VolHistorique::createFromVol($vol,null, $this->container->get('security.token_storage')->getToken()->getUser());
                            $volHistoriqueNew->setVolHistorique($vol);
                            $vol->getVolHistoriqueParentable()->updateFromVol($vol);
                            //Pas de parent send donc on peut supprimer le vol si pendingCancel
                            if("pendingCancel" == $vol->getPeriodeDeVol()->getEtat()){
                                $idVol = $vol->getId();
                                $em->remove($vol);
//                                    $em->flush();
//                                    if($vol->getVolHistoriqueParentable()->getId() != $idVol){
//                                        $em->persist($vol->getVolHistoriqueParentable());
                                $em->remove($vol->getVolHistoriqueParentable());
//                                        $em->flush();
//                                    }
                                $deleteVol = 1;
                            }
                        }
	                    //declenche le onchange de l'extension DOCTRINE
	                    $vol->setDateTimeModification(new \DateTime());
                        if(!$deleteVol) {
                            $em->persist($vol);
                        }
                    }
                    break;
                case 'choix_delestage':
                    foreach ($aIdVols as $idVol) {
                        $deleteVol = 0;
                        /** @var Vol $vol */
                        $vol = $repoVol->find($idVol);
                        /** @var Vol $volOrigine */
                        $volOrigine = clone $vol;

//                        if($vol->getPeriodeDeVol()->getEtat() == "pendingSend" || $vol->getPeriodeDeVol()->getEtat()=="pendingCancel")
//                        {
//                            $vol->delestagePeriode($em);
//                        }else{
                            $periodeVol = $vol->getPeriodeDeVol();

                            $aJoursSemaine = explode("_",$request->get("jour_val"));

                            $aJourValidite = array();
                            foreach ($aJoursSemaine as $jour){
                                if($jour != "-"){
                                    $aJourValidite[] = $jour;
                                }
                            }
                            $jourValiditeDuVol = $periodeVol->getJoursDeValidite();

                            $aJourAmodifer = array();
                            if(sizeof($aJourValidite)==0){
                                $aJourAmodifer = $jourValiditeDuVol;
                            }else{
                                $compt =0;

                                foreach ($aJourValidite as $key=>$jour){
                                    if(in_array($jour,$jourValiditeDuVol)){
                                        $compt ++;
                                        $aJourAmodifer[] = $jour;
                                    }
                                }

                                if(sizeof($aJourAmodifer) == 0){
                                    $request->getSession()->getFlashBag()->add('warning', 'Nous n\'avons pas trouvé de jour à modifier correspondant à vos filtres.');
                                    return $this->redirect($_SERVER['HTTP_REFERER'],'302');
                                }
                            }

                            $debutFilre = $request->get('debut_filtre');
                            if($debutFilre != ""){
                                $debut = new \DateTime($debutFilre);

                                if($debut<$periodeVol->getDateDebut()){
                                    $debut = $periodeVol->getDateDebut();
                                }
                            }else{
                                $debut = $periodeVol->getDateDebut();
                            }

                            $finFilre = $request->get('fin_filtre');
                            if($finFilre != ""){
                                $fin = new \DateTime($finFilre);

                                if($fin>$periodeVol->getDateFin()){
                                    $fin = $periodeVol->getDateFin();
                                }
                            }else{
                                $fin = $periodeVol->getDateFin();
                            }

                            $periodeVol->setDateDebut($debut);
                            $periodeVol->setDateFin($fin);

                            $periodeVol->setEtat("pendingCancel");

                            $periodeVol->setJoursDeValidite($aJourAmodifer);
                            $vol->setPeriodeDeVol($periodeVol);
                            $vol->setModificationPonctuelle(1);

                            $vol->updateVolPonctuel($volOrigine,$em );
                            $periodeVol->corrigerJourDeValidite(true);
                            $periodeVol->corrigerDate(true);
                            $volHistoriqueRoot = $volOrigine->getVolHistoriqueParentable()->getRoot();
                            if("send" == $volHistoriqueRoot->getPeriodeDeVol()->getEtat()){
                                $volhistoriqueachercher = $volOrigine->getVolHistoriqueParentable();

                                while(true){
                                    $parent = $volhistoriqueachercher->getParent();

                                    if($parent==null){
                                        if("send" == $volhistoriqueachercher->getPeriodeDeVol()->getEtat()){
                                            $dernierVolDeHistoriqueAEtatConnu = $volhistoriqueachercher;
                                        }else{
                                            $dernierVolDeHistoriqueAEtatConnu = null;
                                        }

                                        break;
                                    }

                                    $etat = $parent->getPeriodeDeVol()->getEtat();
                                    if($etat=='send'){
                                        $dernierVolDeHistoriqueAEtatConnu = $parent;
                                        break;
                                    }
                                    $volhistoriqueachercher = $parent;
                                }
                                if ($volOrigine->getPeriodeDeVol()->getEtat() == "pendingSend") {
                                    $vol->setAvion($dernierVolDeHistoriqueAEtatConnu->getAvion());
                                    $periodeVol->setDecollage($dernierVolDeHistoriqueAEtatConnu->getPeriodeDeVol()->getDecollage());
                                    $periodeVol->setAtterissage($dernierVolDeHistoriqueAEtatConnu->getPeriodeDeVol()->getAtterissage());
                                    $vol->setCompagnie($dernierVolDeHistoriqueAEtatConnu->getCompagnie());
                                    $vol->setLigne($dernierVolDeHistoriqueAEtatConnu->getLigne());
                                    $vol->setAffretement($dernierVolDeHistoriqueAEtatConnu->getAffretement());
                                    $vol->setNumero($dernierVolDeHistoriqueAEtatConnu->getNumero());
                                    $vol->setTypeDeVol($dernierVolDeHistoriqueAEtatConnu->getTypeDeVol());
                                    $vol->setCodesShareFromList($dernierVolDeHistoriqueAEtatConnu->getCodesShareVol()->toArray());
                                }

                                $vol->setModificationPonctuelle(1);
                                $volHistoriqueNew = VolHistorique::createFromVol($vol,null, $this->container->get('security.token_storage')->getToken()->getUser());
                                Vol::saveVolInHistorique($volHistoriqueNew,$em,$volOrigine->getVolHistoriqueParentable());
                                $vol->setVolHistoriqueParentable($volHistoriqueNew);
                                $volHistoriqueNew->setVolHistorique($vol);
                            }else{
                                $volHistoriqueNew = VolHistorique::createFromVol($vol,null, $this->container->get('security.token_storage')->getToken()->getUser());
                                $volHistoriqueNew->setVolHistorique($vol);
                                $vol->getVolHistoriqueParentable()->updateFromVol($vol);
                                //Pas de parent send donc on peut supprimer le vol si pendingCancel
                                if("pendingCancel" == $vol->getPeriodeDeVol()->getEtat()){
                                    $idVol = $vol->getId();
                                    $em->remove($vol);
//                                    $em->flush();
//                                    if($vol->getVolHistoriqueParentable()->getId() != $idVol){
//                                        $em->persist($vol->getVolHistoriqueParentable());
                                        $em->remove($vol->getVolHistoriqueParentable());
//                                        $em->flush();
//                                    }
                                    $deleteVol = 1;
                                }
                            }
	                    //declenche le onchange de l'extension DOCTRINE
	                    $vol->setDateTimeModification(new \DateTime());
                            if(!$deleteVol) {
                                $em->persist($vol);
                            }
//                        }
                    }
                    break;
                case 'choix_retarder':
                    $minutes = (60 * $request->get('heure')) + $request->get('minute');
                    foreach ($aIdVols as $idVol) {
                        $deleteVol = 0;
                        /** @var Vol $vol */
                        $vol = $repoVol->find($idVol);
                        /** @var Vol $newVol */
                        $volOrigine = clone $vol;
                        $periodeVol = $vol->getPeriodeDeVol();

                        $aJoursSemaine = explode("_",$request->get("jour_val"));

                        $aJourValidite = array();
                        foreach ($aJoursSemaine as $jour){
                            if($jour != "-"){
                                $aJourValidite[] = $jour;
                            }
                        }
                        $jourValiditeDuVol = $periodeVol->getJoursDeValidite();

                        $aJourAmodifer = array();
                        if(sizeof($aJourValidite)==0){
                            $aJourAmodifer = $jourValiditeDuVol;
                        }else{
                            $compt =0;

                            foreach ($aJourValidite as $key=>$jour){
                                if(in_array($jour,$jourValiditeDuVol)){
                                    $compt ++;
                                    $aJourAmodifer[] = $jour;
                                }
                            }

                            if(sizeof($aJourAmodifer) == 0){
                                $request->getSession()->getFlashBag()->add('warning', 'Nous n\'avons pas trouvé de jour à modifier correspondant à vos filtres.');
                                return $this->redirect($_SERVER['HTTP_REFERER'],'302');
                            }
                        }

                        $currentvalueDecollage = clone $periodeVol->getDecollage();
                        $currentvalueDecollage->add(new \DateInterval('PT' . $minutes . 'M'));
                        $periodeVol->setDecollage($currentvalueDecollage);

                        $currentvalueAtterissage = clone $periodeVol->getAtterissage();
                        $currentvalueAtterissage->add(new \DateInterval('PT' . $minutes . 'M'));
                        $periodeVol->setAtterissage($currentvalueAtterissage);

                        $debutFilre = $request->get('debut_filtre');
                        if($debutFilre != ""){
                            $debut = new \DateTime($debutFilre);

                            if($debut<$periodeVol->getDateDebut()){
                                $debut = $periodeVol->getDateDebut();
                            }
                        }else{
                            $debut = $periodeVol->getDateDebut();
                        }

                        $finFilre = $request->get('fin_filtre');
                        if($finFilre != ""){
                            $fin = new \DateTime($finFilre);

                            if($fin>$periodeVol->getDateFin()){
                                $fin = $periodeVol->getDateFin();
                            }
                        }else{
                            $fin = $periodeVol->getDateFin();
                        }

                        $periodeVol->setDateDebut($debut);
                        $periodeVol->setDateFin($fin);

                        $etat = $this->getEtatApresMaj($volOrigine,$vol);

                        $periodeVol->setEtat($etat);

                        $periodeVol->setJoursDeValidite($aJourAmodifer);
                        $vol->setPeriodeDeVol($periodeVol);

                        $vol->updateVolPonctuel($volOrigine,$em );
                        $periodeVol->corrigerJourDeValidite(true);
                        $periodeVol->corrigerDate(true);
                        $volHistoriqueRoot = $volOrigine->getVolHistoriqueParentable()->getRoot();
                        if("send" == $volHistoriqueRoot->getPeriodeDeVol()->getEtat()){
                            $vol->setModificationPonctuelle(1);
                            $volHistoriqueNew = VolHistorique::createFromVol($vol,null, $this->container->get('security.token_storage')->getToken()->getUser());
                            Vol::saveVolInHistorique($volHistoriqueNew,$em,$volOrigine->getVolHistoriqueParentable());
                            $vol->setVolHistoriqueParentable($volHistoriqueNew);
                            $volHistoriqueNew->setVolHistorique($vol);
	                        //declenche le onchange de l'extension DOCTRINE
	                        $vol->setDateTimeModification(new \DateTime());
                            $em->persist($vol);
                        }else{
                            $volHistoriqueNew = VolHistorique::createFromVol($vol,null, $this->container->get('security.token_storage')->getToken()->getUser());
                            $volHistoriqueNew->setVolHistorique($vol);
                            $vol->getVolHistoriqueParentable()->updateFromVol($vol);
                            //Pas de parent send donc on peut supprimer le vol si pendingCancel
                            if("pendingCancel" == $vol->getPeriodeDeVol()->getEtat()){
                                $idVol = $vol->getId();
                                $em->remove($vol);
//                                    $em->flush();
//                                    if($vol->getVolHistoriqueParentable()->getId() != $idVol){
//                                        $em->persist($vol->getVolHistoriqueParentable());
                                $em->remove($vol->getVolHistoriqueParentable());
//                                        $em->flush();
//                                    }
                                $deleteVol = 1;
                            }
                        }
                        $vol->setDateTimeModification(new \DateTime());
                        if(!$deleteVol) {
                            $em->persist($vol);
                        }
                    }
                    break;
                case 'choix_avancer':
                    $minutes = (60 * $request->get('heure')) + $request->get('minute');
                    foreach ($aIdVols as $idVol) {
                        $deleteVol = 0;
                        /** @var Vol $vol */
                        $vol = $repoVol->find($idVol);
                        /** @var Vol $newVol */
                        $volOrigine = clone $vol;
                        $periodeVol = $vol->getPeriodeDeVol();

                        $aJoursSemaine = explode("_",$request->get("jour_val"));

                        $aJourValidite = array();
                        foreach ($aJoursSemaine as $jour){
                            if($jour != "-"){
                                $aJourValidite[] = $jour;
                            }
                        }
                        $jourValiditeDuVol = $periodeVol->getJoursDeValidite();

                        $aJourAmodifer = array();
                        if(sizeof($aJourValidite)==0){
                            $aJourAmodifer = $jourValiditeDuVol;
                        }else{
                            $compt =0;

                            foreach ($aJourValidite as $key=>$jour){
                                if(in_array($jour,$jourValiditeDuVol)){
                                    $compt ++;
                                    $aJourAmodifer[] = $jour;
                                }
                            }

                            if(sizeof($aJourAmodifer) == 0){
                                $request->getSession()->getFlashBag()->add('warning', 'Nous n\'avons pas trouvé de jour à modifier correspondant à vos filtres.');
                                return $this->redirect($_SERVER['HTTP_REFERER'],'302');
                            }
                        }

                        $currentvalueDecollage = clone $periodeVol->getDecollage();
                        $currentvalueDecollage->sub(new \DateInterval('PT' . $minutes . 'M'));
                        $periodeVol->setDecollage($currentvalueDecollage);

                        $currentvalueAtterissage = clone $periodeVol->getAtterissage();
                        $currentvalueAtterissage->sub(new \DateInterval('PT' . $minutes . 'M'));
                        $periodeVol->setAtterissage($currentvalueAtterissage);

                        $debutFilre = $request->get('debut_filtre');
                        if($debutFilre != ""){
                            $debut = new \DateTime($debutFilre);

                            if($debut<$periodeVol->getDateDebut()){
                                $debut = $periodeVol->getDateDebut();
                            }
                        }else{
                            $debut = $periodeVol->getDateDebut();
                        }

                        $finFilre = $request->get('fin_filtre');
                        if($finFilre != ""){
                            $fin = new \DateTime($finFilre);

                            if($fin>$periodeVol->getDateFin()){
                                $fin = $periodeVol->getDateFin();
                            }
                        }else{
                            $fin = $periodeVol->getDateFin();
                        }

                        $periodeVol->setDateDebut($debut);
                        $periodeVol->setDateFin($fin);

                        $etat = $this->getEtatApresMaj($volOrigine,$vol);

                        $periodeVol->setEtat($etat);

                        $periodeVol->setJoursDeValidite($aJourAmodifer);
                        $vol->setPeriodeDeVol($periodeVol);

                        $vol->updateVolPonctuel($volOrigine,$em );

                        $periodeVol->corrigerJourDeValidite(true);
                        $periodeVol->corrigerDate(true);
                        $volHistoriqueRoot = $volOrigine->getVolHistoriqueParentable()->getRoot();
                        if("send" == $volHistoriqueRoot->getPeriodeDeVol()->getEtat()){
                            $vol->setModificationPonctuelle(1);
                            $volHistoriqueNew = VolHistorique::createFromVol($vol,null, $this->container->get('security.token_storage')->getToken()->getUser());
                            Vol::saveVolInHistorique($volHistoriqueNew,$em,$volOrigine->getVolHistoriqueParentable());
                            $vol->setVolHistoriqueParentable($volHistoriqueNew);
                            $volHistoriqueNew->setVolHistorique($vol);
                        }else{
                            $volHistoriqueNew = VolHistorique::createFromVol($vol,null, $this->container->get('security.token_storage')->getToken()->getUser());

                            $volHistoriqueNew->setVolHistorique($vol);
                            $vol->getVolHistoriqueParentable()->updateFromVol($vol);
                            //Pas de parent send donc on peut supprimer le vol si pendingCancel
                            if("pendingCancel" == $vol->getPeriodeDeVol()->getEtat()){
                                $idVol = $vol->getId();
                                $em->remove($vol);
//                                    $em->flush();
//                                    if($vol->getVolHistoriqueParentable()->getId() != $idVol){
//                                        $em->persist($vol->getVolHistoriqueParentable());
                                $em->remove($vol->getVolHistoriqueParentable());
//                                        $em->flush();
//                                    }
                                $deleteVol = 1;
                            }
                        }
                        //declenche le onchange de l'extension DOCTRINE
	                    $vol->setDateTimeModification(new \DateTime());
                        if(!$deleteVol) {
                            $vol->setNumero($vol->getNumero());
                            $em->persist($vol);
                        }
                    }
                    break;

                case 'choix_horaires':
                    foreach ($aIdVols as $idVol) {
                        $deleteVol = 0;
                        /** @var Vol $vol */
                        $vol = $repoVol->find($idVol);

                        $avion = $vol->getAvion();

                        /** @var Vol $newVol */
                        $volOrigine = clone $vol;
                        $periodeVol = $vol->getPeriodeDeVol();

                        $aJoursSemaine = explode("_",$request->get("jour_val"));

                        $aJourValidite = array();
                        foreach ($aJoursSemaine as $jour){
                            if($jour != "-"){
                                $aJourValidite[] = $jour;
                            }
                        }
                        $jourValiditeDuVol = $periodeVol->getJoursDeValidite();

                        $aJourAmodifer = array();
                        if(sizeof($aJourValidite)==0){
                            $aJourAmodifer = $jourValiditeDuVol;
                        }else{
                            $compt =0;

                            foreach ($aJourValidite as $key=>$jour){
                                if(in_array($jour,$jourValiditeDuVol)){
                                    $compt ++;
                                    $aJourAmodifer[] = $jour;
                                }
                            }

                            if(sizeof($aJourAmodifer) == 0){
                                $request->getSession()->getFlashBag()->add('warning', 'Nous n\'avons pas trouvé de jour à modifier correspondant à vos filtres.');
                                return $this->redirect($_SERVER['HTTP_REFERER'],'302');
                            }
                        }

                        $heureMinuteDepart = $request->get("heureminute_depart");
                        $heureMinuteArrivee = $request->get("heureminute_arrivee");

                        $aHeureMinuteDepart = explode(":",$heureMinuteDepart);
                        if(sizeof($aHeureMinuteDepart)<2){
                            $aHeureMinuteDepart[0] = "00";
                            $aHeureMinuteDepart[1] = "00";
                        }
                        $aHeureMinuteArrivee = explode(":",$heureMinuteArrivee);
                        if(sizeof($aHeureMinuteArrivee)<2){
                            $aHeureMinuteArrivee[0] = "00";
                            $aHeureMinuteArrivee[1] = "00";
                        }


                        if(($aHeureMinuteDepart[0] == "00" && $aHeureMinuteDepart[1] == "00") &&
                            ($aHeureMinuteArrivee[0] != "00" || $aHeureMinuteArrivee[0] != "00")){
                            $nouveauTempsDeVol = $this->ajaxGetTempdsDeVolAction($volOrigine->getLigne()->getAeroportDepart(),$volOrigine->getLigne()->getAeroportArrivee(),$avion,$volOrigine->getPeriodeDeVol()->getDateDebut(),$volOrigine->getPeriodeDeVol()->getDateFin());
                            $nouveauTempsDeVol = substr(explode(":",$nouveauTempsDeVol->getContent())[1],0,-1);
                            if($nouveauTempsDeVol == "null"){
                                //Valeur par défaut
                                $nouveauTempsDeVol = "59";
//                                $request->getSession()->getFlashBag()->add('warning', 'Une erreure s\'est produite. Aucun temps de vol trouver. Veuillez vérifier qu\'il existe bien un temps de vol pour le vol et l\'avion sélectionné.');
//                                return $this->redirect($_SERVER['HTTP_REFERER'],'302');
                            }
                            $arrivee = clone $volOrigine->getPeriodeDeVol()->getAtterissage();
                            $arrivee->setTime($aHeureMinuteArrivee[0],$aHeureMinuteArrivee[1]);
                            $depart = $arrivee;
                            $depart->sub(new \DateInterval("PT".$nouveauTempsDeVol."M"));

//                            $depart = clone $arrivee;
//                            $vol->getPeriodeDeVol()->setDecollage($arrivee);
                        }else {
                            /** @var \DateTime $depart */
                            $depart = clone $volOrigine->getPeriodeDeVol()->getDecollage();
                            $depart->setTime($aHeureMinuteDepart[0], $aHeureMinuteDepart[1]);
                        }

                        if(($aHeureMinuteDepart[0] != "00" || $aHeureMinuteDepart[1] != "00") &&
                            ($aHeureMinuteArrivee[0] == "00" && $aHeureMinuteArrivee[1] == "00")){
                            $nouveauTempsDeVol = $this->ajaxGetTempdsDeVolAction($volOrigine->getLigne()->getAeroportDepart(),$volOrigine->getLigne()->getAeroportArrivee(),$avion,$volOrigine->getPeriodeDeVol()->getDateDebut(),$volOrigine->getPeriodeDeVol()->getDateFin());
                            $nouveauTempsDeVol = substr(explode(":",$nouveauTempsDeVol->getContent())[1],0,-1);
                            if($nouveauTempsDeVol == "null"){
                                $nouveauTempsDeVol = "59";
//                                $request->getSession()->getFlashBag()->add('warning', 'Une erreure s\'est produite. Aucun temps de vol trouver. Veuillez vérifier qu\'il existe bien un temps de vol pour le vol et l\'avion sélectionné.');
//                                return $this->redirect($_SERVER['HTTP_REFERER'],'302');
                            }
                            $depart = clone $volOrigine->getPeriodeDeVol()->getDecollage();
                            $depart->setTime($aHeureMinuteDepart[0], $aHeureMinuteDepart[1]);
                            $arrivee = clone $depart;
                            $arrivee->add(new \DateInterval("PT".$nouveauTempsDeVol."M"));

//                            $arrivee = clone  $depart;
//                            $vol->getPeriodeDeVol()->setAtterissage($depart);
                        }else{
                            /** @var \DateTime $arrivee */
                            $arrivee = clone $volOrigine->getPeriodeDeVol()->getAtterissage();
                            $arrivee->setTime($aHeureMinuteArrivee[0],$aHeureMinuteArrivee[1]);
                        }



                        $debutFilre = $request->get('debut_filtre');
                        if($debutFilre != ""){
                            $debut = new \DateTime($debutFilre);

                            if($debut<$periodeVol->getDateDebut()){
                                $debut = $periodeVol->getDateDebut();
                            }
                        }else{
                            $debut = $periodeVol->getDateDebut();
                        }

                        $finFilre = $request->get('fin_filtre');
                        if($finFilre != ""){
                            $fin = new \DateTime($finFilre);

                            if($fin>$periodeVol->getDateFin()){
                                $fin = $periodeVol->getDateFin();
                            }
                        }else{
                            $fin = $periodeVol->getDateFin();
                        }

                        $periodeVol->setDateDebut($debut);
                        $periodeVol->setDateFin($fin);
                        $periodeVol->setDecollage($depart);
                        $periodeVol->setAtterissage($arrivee);

                        $etat = $this->getEtatApresMaj($volOrigine,$vol);

                        $periodeVol->setEtat($etat);

                        $periodeVol->setJoursDeValidite($aJourAmodifer);
                        $vol->setPeriodeDeVol($periodeVol);


                        $vol->updateVolPonctuel($volOrigine,$em );
                        $periodeVol->corrigerJourDeValidite(true);
                        $periodeVol->corrigerDate(true);
                        $volHistoriqueRoot = $volOrigine->getVolHistoriqueParentable()->getRoot();
                        if("send" == $volHistoriqueRoot->getPeriodeDeVol()->getEtat()){
                            $vol->setModificationPonctuelle(1);
                            $volHistoriqueNew = VolHistorique::createFromVol($vol,null, $this->container->get('security.token_storage')->getToken()->getUser());
                            Vol::saveVolInHistorique($volHistoriqueNew,$em,$volOrigine->getVolHistoriqueParentable());
                            $vol->setVolHistoriqueParentable($volHistoriqueNew);
                            $volHistoriqueNew->setVolHistorique($vol);
                        }else{
                            $volHistoriqueNew = VolHistorique::createFromVol($vol,null, $this->container->get('security.token_storage')->getToken()->getUser());
                            $volHistoriqueNew->setVolHistorique($vol);
                            $vol->getVolHistoriqueParentable()->updateFromVol($vol);
                            //Pas de parent send donc on peut supprimer le vol si pendingCancel
                            if("pendingCancel" == $vol->getPeriodeDeVol()->getEtat()){
                                $idVol = $vol->getId();
                                $em->remove($vol);
//                                    $em->flush();
//                                    if($vol->getVolHistoriqueParentable()->getId() != $idVol){
//                                        $em->persist($vol->getVolHistoriqueParentable());
                                $em->remove($vol->getVolHistoriqueParentable());
//                                        $em->flush();
//                                    }
                                $deleteVol = 1;
                            }
                        }
	                    //declenche le onchange de l'extension DOCTRINE
	                    $vol->setDateTimeModification(new \DateTime());
                        if(!$deleteVol) {
                            $em->persist($vol);
                        }
                    }
                    break;
        }


//        if($request->get('filtre_date_debut') && $request->get('date_debut') != ''){
//            foreach ($aIdVols as $idVol) {
//                /** @var Vol $vol */
//                $vol = $repoVol->find($idVol);
//                /** @var Vol $newVol */
//                $volOrigine = clone $vol;
//                $periodeVol = $vol->getPeriodeDeVol();
//                $periodeVol->setDateDebut(\DateTime::createFromFormat('j-m-Y',$request->get('date_debut')));
//
//                $vol->updateVol($volOrigine,$em );
//            }
//        }
//
//        if($request->get('filtre_date_fin') && $request->get('date_fin') != ''){
//            foreach ($aIdVols as $idVol) {
//                /** @var Vol $vol */
//                $vol = $repoVol->find($idVol);
//                /** @var Vol $newVol */
//                $volOrigine = clone $vol;
//                $periodeVol = $vol->getPeriodeDeVol();
//                $periodeVol->setDateFin(\DateTime::createFromFormat('j-m-Y',$request->get('date_fin')));
//
//                $vol->updateVol($volOrigine,$em );
//            }
//        }
//
//        if($request->get('type_vol_val')){
//            foreach ($aIdVols as $idVol) {
//                /** @var Vol $vol */
//                $vol = $repoVol->find($idVol);
//                /** @var Vol $newVol */
//                $volOrigine = clone $vol;
//
//                $repoTypeDeVol = $em->getRepository('AirCorsicaXKPlanBundle:TypeDeVol');
//                $typeDeVol = $repoTypeDeVol->find($request->get('type_vol_val'));
//                $vol->setTypeDeVol($typeDeVol);
//
//                $vol->updateVol($volOrigine,$em );
//            }
//        }
//
//        if($request->get('nature_vol_val')){
//            foreach ($aIdVols as $idVol) {
//                /** @var Vol $vol */
//                $vol = $repoVol->find($idVol);
//                /** @var Vol $newVol */
//                $volOrigine = clone $vol;
//
//                $repoNtureDeVol = $em->getRepository('AirCorsicaXKPlanBundle:NatureDeVol');
//                $natureDeVol = $repoNtureDeVol->find($request->get('nature_vol_val'));
//                $vol->addNaturesDeVol($natureDeVol);
//
//                $vol->updateVol($volOrigine,$em );
//            }
//        }
//
//        if($request->get('jour_val') && $request->get('jour_val')!= "-_-_-_-_-_-_-"){
//            foreach ($aIdVols as $idVol) {
//                /** @var Vol $vol */
//                $vol = $repoVol->find($idVol);
//                /** @var Vol $newVol */
//                $volOrigine = clone $vol;
//                /** @var PeriodeDeVol $periode */
//                $periode = $vol->getPeriodeDeVol();
//                $periode->setJoursDeValidite(explode("_",$request->get('jour_val')));
//
//                $vol->updateVol($volOrigine,$em );
//            }
//        }

        //A voir ?
        //$referer .= "&avion_ponctuel=".$request->get('avion_ponctuel');
        $em->flush();
        return $this->redirect($referer);
    }

    /**
     * Timetable vol entities.
     *
     */
    public function timetableAction(Request $request, $export = null)
    {

	    //Uniquement pour ce script
	    ini_set('max_execution_time', 0);

        if($request->query->has('rafraichir')){

            $session = $request->getSession();
            $session->set("queryString",$request->getQueryString());

            $formatDate=$request->get('format');
            $em = $this->getDoctrine()->getManager();
            /** @var VolRepository $repoVol */
            $repoVol = $em->getRepository('AirCorsicaXKPlanBundle:Vol');

            $orderParPeriode = "ASC";

            $repoTemplate = $em->getRepository('AirCorsicaXKPlanBundle:Template');
            $templateCurrent = $repoTemplate->find($this->get('session')->get('template'));

            $repoLigne = $em->getRepository('AirCorsicaXKPlanBundle:Ligne');

            $aeroportCorse = array('AJA','BIA','CLY','FSC');
            $aLigne1 = $em->getRepository('AirCorsicaXKPlanBundle:Ligne')->findAllByOrdre();
            $aLigne2 = $em->getRepository('AirCorsicaXKPlanBundle:Ligne')->findAllNonOrdonnee($aeroportCorse,"IN");
            $aLigne3 = $em->getRepository('AirCorsicaXKPlanBundle:Ligne')->findAllNonOrdonnee($aeroportCorse,"NOT IN");

//            $aLigne = array_merge($em->getRepository('AirCorsicaXKPlanBundle:Ligne')->findAllByOrdre(),$em->getRepository('AirCorsicaXKPlanBundle:Ligne')->findAllNonOrdonnee($aeroportCorse,"IN"),$em->getRepository('AirCorsicaXKPlanBundle:Ligne')->findAllNonOrdonnee($aeroportCorse,"NOT IN"));

            $aLigne = array_merge($aLigne1, $aLigne2 , $aLigne3);
//            array_push($aLigne,$aLigne1);
//            array_push($aLigne,$aLigne2);
//            array_push($aLigne,$aLigne3);
//            $aLigne = array_merge($em->getRepository('AirCorsicaXKPlanBundle:Ligne')->findAllByOrdre(),$em->getRepository('AirCorsicaXKPlanBundle:Ligne')->findAllNonOrdonnee());

            $aaAeroport = array();
            foreach ($aLigne as $ligne){
                if(array_key_exists($ligne->getAeroportDepart()->getId()."-".$ligne->getAeroportArrivee()->getId(),$aaAeroport)
                    || array_key_exists($ligne->getAeroportArrivee()->getId()."-".$ligne->getAeroportDepart()->getId(),$aaAeroport))
                {
                    if(isset($aaAeroport[$ligne->getAeroportDepart()->getId()."-".$ligne->getAeroportArrivee()->getId()])) {
                        if (!in_array($ligne->getAeroportDepart()->getCodeIATA() . "-" . $ligne->getAeroportArrivee()->getCodeIATA(), $aaAeroport[$ligne->getAeroportDepart()->getId() . "-" . $ligne->getAeroportArrivee()->getId()])) {
                            $aaAeroport[$ligne->getAeroportDepart()->getId() . "-" . $ligne->getAeroportArrivee()->getId()][] = $ligne->getAeroportDepart()->getCodeIATA() . "-" . $ligne->getAeroportArrivee()->getCodeIATA();
                        }
                    }
                    if(isset($aaAeroport[$ligne->getAeroportArrivee()->getId()."-".$ligne->getAeroportDepart()->getId()])) {
                        if (!in_array($ligne->getAeroportDepart()->getCodeIATA() . "-" . $ligne->getAeroportArrivee()->getCodeIATA(), $aaAeroport[$ligne->getAeroportArrivee()->getId() . "-" . $ligne->getAeroportDepart()->getId()])) {
                            $aaAeroport[$ligne->getAeroportArrivee()->getId() . "-" . $ligne->getAeroportDepart()->getId()][] = $ligne->getAeroportDepart()->getCodeIATA() . "-" . $ligne->getAeroportArrivee()->getCodeIATA();
                        }
                    }
                }else{
                    $aaAeroport[$ligne->getAeroportDepart()->getId() . "-" . $ligne->getAeroportArrivee()->getId()][] = $ligne->getAeroportDepart()->getCodeIATA() . "-" . $ligne->getAeroportArrivee()->getCodeIATA();
                }
            }

            //back request
            $aeroportDepart = $request->get('aeroport_aller');
            $aeroportArrivee = $request->get('aeroport_retour');

            $aVols = array();
            foreach ($aaAeroport as $aAeroport){
                foreach ($aAeroport as $aeroport)
                {
                    $adepartArrivee = explode("-",$aeroport);

                    if($aeroportDepart != ""){
                        if($aeroportDepart != $adepartArrivee[0]){
                            continue;
                        }
                    }
                    if($aeroportArrivee != ""){
                        if($aeroportArrivee != $adepartArrivee[1]){
                            continue;
                        }
                    }

                    $request->query->set('aeroport_aller',$adepartArrivee[0]);
                    $request->query->set('aeroport_retour',$adepartArrivee[1]);

                    //Uniquement les non deleste
                    $request->query->add(array('deleste'=>0));

                    if($request->get('retour') && $request->get('aller')){
                        //On triche pour l'un après l'autre sinon cela casse le tri du time table
                        $request->query->remove('retour');
                        $vols = $repoVol->getVolsFilter($request,$templateCurrent,$orderParPeriode);
                        $request->query->add(array('retour'=>'on'));
                        $request->query->remove('aller');
                        $vols = array_merge($vols,$repoVol->getVolsFilter($request,$templateCurrent,$orderParPeriode));
                        $request->query->add(array('aller'=>'on'));
                    }else{
                        $vols = $repoVol->getVolsFilter($request,$templateCurrent,$orderParPeriode);
                    }

                    $options = array();
                    if($fusionPeriode=$request->get('fusionnerPeriode')==1){
                        $options[] = 'fusionnerPeriode';
                    }
                    if($fusionTypeAvion=$request->get('fusionnerTypeAvion')==1){
                        $options[] = 'fusionnerTypeAvion';
                    }
                    $options['GMT'] = '0';
                    if(($GMT=$request->get('GMT'))!=null){
                        $options['GMT'] = $GMT;
                    }
                    if(sizeof($vols)) {
                        $aVols = array_merge($aVols,Vol::fusionnerVolsOptimise($vols, $options));
                    }
                }
            }
            $request->query->set('aeroport_aller',$aeroportDepart);
            $request->query->set('aeroport_retour',$aeroportArrivee);

            if(isset($export)){
                switch ($export){
                    case 'csv':
                        $res = array();
                        foreach ($aVols as $vol){
                            /** @var Vol $vol */
                            //$vol->getPeriodeDeVol()->getEtat();
                            $res[$vol->getLigne()->getAeroportDepart()->getCodeIATA()."-".$vol->getLigne()->getAeroportArrivee()->getCodeIATA()][] = $vol;
                        }
                        return $res;
                        break;
                    case 'pdf':
                        if(($GMT=$request->get('GMT'))!=null){
                            if($GMT == 0){
                                $GMTtoPdf = "Local Time";
                            }elseif(!strpos($GMT,"-") && $GMT != 0){
                                $GMTtoPdf = "+".$GMT;
                            }else{
                                $GMTtoPdf = $GMT;
                            }
                        }
                        $res = array();
                        foreach ($aVols as $vol){
                            $res[$vol->getLigne()->getAeroportDepart()->getCodeIATA()."-".$vol->getLigne()->getAeroportArrivee()->getCodeIATA()][] = $vol;
                        }
                        return $this->renderView('AirCorsicaXKPlanBundle:Vol:timetablepdf.html.twig', array(
                            'aaVols' => $res,
                            'GMT' => $GMTtoPdf,
                            'formatDate'     => $formatDate
                        ));
                        break;
                    default:
                        throw new \LogicException("Format d'export non géré !");
                }
            }
        }else{
            $aVols = array();
            $formatDate = null;
            $session = $request->getSession();
            $session->remove("queryString");
        }

        return $this->render('AirCorsicaXKPlanBundle:Vol:timetable.html.twig', array(
            'aVols' => $aVols,
            'formatDate'     => $formatDate,
            'optionTimeTable' => 1
        ));
    }

    /**
     * liste des vols
     *
     */
    public function listeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoTemplate = $em->getRepository('AirCorsicaXKPlanBundle:Template');
        if($request->query->has('rafraichir')){

            //récupération des variables de sessions
            $session = $request->getSession();
            $tempURI = explode('?',$_SERVER['REQUEST_URI']);

            if($session->has('vm_volsfilter')) {
                //si l'attribut vm_volsfilter existe dans la variable de session, on l'efface
                $session->remove('vm_volsfilter');
                //on enregistre la nouvelle valeur
                //$session->set('vm_volsfilter',$paramFilter);
                $session->set('vm_volsfilter',$tempURI[1]);
            }else{
                //on enregistre la valeur
                //$session->set('vm_volsfilter',$paramFilter);
                $session->set('vm_volsfilter',$tempURI[1]);
            }

            $repoVol = $em->getRepository('AirCorsicaXKPlanBundle:Vol');
            $templateCurrent = $repoTemplate->find($this->get('session')->get('template'));
            $request->request->add(array('ordreListeVol' => 1));
            $vols = $repoVol->getVolsFilter($request,$templateCurrent,"ASC");

            $session->set("queryString",$request->getQueryString());
        }else{
            $session = $request->getSession();
            $session->remove("queryString");
            $vols = array();
        }

        //$templates = $repoTemplate->findAll();
        //On cherche uniquement les templates actifs (cad inactif = 0)
        $criteria = array('inactif'=>'0');
        $templates = $repoTemplate->findBy($criteria);

        return $this->render('AirCorsicaXKPlanBundle:Vol:liste.html.twig',array(
            'vols'      => $vols,
            'templates' => $templates,
        ));
    }


    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sidebarAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAeroport = $em->getRepository('AirCorsicaXKPlanBundle:Aeroport');
        $aeroports = $repoAeroport->findAll();

        $repoAvion = $em->getRepository('AirCorsicaXKPlanBundle:Avion');
        $avions = $repoAvion->findAllTrieParAffreteOrdreNom();

        $repoTypeVol = $em->getRepository('AirCorsicaXKPlanBundle:TypeDeVol');
        $type_vol = $repoTypeVol->findAll();

        $repoNatureVol = $em->getRepository('AirCorsicaXKPlanBundle:NatureDeVol');
        $nature_vol = $repoNatureVol->findAll();

        $repoSaison = $em->getRepository('AirCorsicaXKPlanBundle:Saison');
        $saisons = $repoSaison->findAll();

        return $this->render(
            '@AirCorsicaXKPlan/Vol/sidebar_vol.html.twig',
            array(
                'aeroports'     => $aeroports,
                'avions'        => $avions,
                'request'       => $request,
                'type_vol'      => $type_vol,
                'nature_vol'    => $nature_vol,
                'saisons'       => $saisons,
                )
        );
    }


    /**
     * historique
     *
     */
    public function historiqueAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoVol = $em->getRepository('AirCorsicaXKPlanBundle:VolHistorique');
        $options = array(
            'decorate' => true,
            'rootOpen' => '<ul>',
            'rootClose' => '</ul>',
            'childOpen' => '<li>',
            'childClose' => '</li>',
            'nodeDecorator' =>
            function($node) use ($repoVol){
                /** @var Vol $oVol */
                $oVol = $repoVol->find($node['id']);
                $format = '%s : %s %s - Départ %s Arivée %s les jours %s avec %s ( %s ) (Modif par = %s) %s';

                $aCoshares = array();
                foreach ($oVol->getCodesShareVol() as $oCodeShare){
                     $aCoshares[] = $oCodeShare->getLibelle();
                }

                if(sizeof($aCoshares)){
                    $listeCodeShare = implode(',',$aCoshares);
                    $pluriel = '';
                    if(sizeof($aCoshares) > 1){
                        $pluriel = 's';
                    }
                    $sCodeShare = "Code".$pluriel." Share ".$listeCodeShare;
                }else{
                    $sCodeShare = "Pas de Code Share";
                }

                $text = sprintf(
                    $format,
                    $oVol->getNumero(),
                    $oVol->getLigne(),
                    $oVol->getPeriodeDeVol(),
                    $oVol->getPeriodeDeVol()->getDecollage()->format('H:i'),
                    $oVol->getPeriodeDeVol()->getAtterissage()->format('H:i'),
                    implode('',$oVol->getPeriodeDeVol()->getJoursDeValidite()),
                    $oVol->getAvion(),
                    $sCodeShare,
                    $oVol->getModificateur(),
                    $oVol->getId());

                if($repoVol->getChildren($oVol)){
                    $dataIDVol = 'data-idvol="'.$oVol->getId().'"';
                    $classContextualMenu = " cancel-historique ";
                }else{
                    $dataIDVol = "";
                    $classContextualMenu = "";
                }
                $html =  ' <a href="/page/">
                             <span class=" '.$classContextualMenu.' fa-stack etat-'.$oVol->getPeriodeDeVol()->getEtat().'" '.$dataIDVol.'> 
                                  <i class="fa fa-envelope-o fa-stack-1x"></i>';

                if("pendingSend" === $oVol->getPeriodeDeVol()->getEtat() || "pendingCancel" === $oVol->getPeriodeDeVol()->getEtat()){
                    $html .=  '<i class="fa fa-ban fa-stack-2x text-danger"></i>';
                }

                 $html .=  '</span>
                             '.$text.'
                          </a>';

                return $html;
            }
        );

        if($request->query->has('rafraichir')){
            $session = $request->getSession();
            $session->set("queryString",$request->getQueryString());

            $em = $this->getDoctrine()->getManager();
            $repoVolH = $em->getRepository('AirCorsicaXKPlanBundle:VolHistorique');

            $repoTemplate = $em->getRepository('AirCorsicaXKPlanBundle:Template');
            $templateCurrent = $repoTemplate->find($this->get('session')->get('template'));
            $vols = $repoVolH->getVolsFilter($request,$templateCurrent);
//            var_dump(sizeof($vols));die;

            //BON CODE pour supprimer l'istorique
            // à exécuter tant qu'il y a une erreur 500
//            $htmlTree = $repoVol->buildTreeV2($vols,$options);
//
//
//            $aIdVol = explode("-",$htmlTree);
//            var_dump(sizeof($aIdVol));
//            foreach ($aIdVol as $id){
//                if($id != "")
//                {
//                    $vol = $em->find('AirCorsicaXKPlanBundle:VolHistorique',$id);
////
//                        echo $vol->getId()."<br/>";
//                        $em->remove($vol);
//                        $em->flush();
////
//                }
//            }
//
//            die;
            //FIN BON CODE

           $htmlTree = $repoVol->buildTree($vols, $options);
        }else{
            $htmlTree = null;
            $session = $request->getSession();
            $session->remove("queryString");
       }

        return $this->render('AirCorsicaXKPlanBundle:Vol:historique.html.twig', array(
            'htmlTree' => $htmlTree,
        ));
    }

    /**
     * Creates a new vol entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request, Vol $volOrigine = null)
    {
        $type = 'default';
        $defaultAvion = '14';
        $em = $this->getDoctrine()->getManager();
        $id_precedent = "";
        $periode_2 = false;
        $dateDebut2 = false;
        $dateFin2 = false;

        if($request->get('volsuivant')){
            if($request->get('periode_2') && $request->get('periode_2') == "true"){
                $periode_2 = true;
            }

            if($request->get('dateDebut2') && $request->get('dateDebut2') != "" && $request->get('dateFin2') && $request->get('dateFin2') != ""){
                $dateDebut2 = $request->get('dateDebut2');
                $dateFin2 = $request->get('dateFin2');
            }
            $repoVolsuivant = $em->getRepository("AirCorsicaXKPlanBundle:Vol");
            /** @var Vol $vol */
            $vol = $repoVolsuivant->find($request->get('volsuivant'));
            $aeroportDepart = $vol->getLigne()->getAeroportDepart();
            $aeroportArrivee = $vol->getLigne()->getAeroportArrivee();
            $aeroportDepart_id = $aeroportArrivee->getId();
            $aeroportArrivee_id = $aeroportDepart->getId();
            $volNew = clone $vol;
            $volNew->getLigne()->setAeroportDepart($aeroportArrivee);
            $volNew->getLigne()->setAeroportArrivee($aeroportDepart);

            $currentDecollage = $vol->getPeriodeDeVol()->getDecollage();
            $currentAtterissage = $vol->getPeriodeDeVol()->getAtterissage();
            $tempsDeVol = $currentDecollage->diff($currentAtterissage);
            $newDecollage = $currentAtterissage->add(new \DateInterval("PT".$vol->getAvion()->getTypeAvion()->getTempsDemiTour()."M"));
            $newAtterissage = clone $currentAtterissage;
            $newAtterissage->add($tempsDeVol);

            $volNew->getPeriodeDeVol()->setDecollage($newDecollage);
            $volNew->getPeriodeDeVol()->setAtterissage($newAtterissage);

            $volNew->setId('');
            $volNew->setNumero('');
            $type = 'suivant';
            $id_precedent = $request->get('volsuivant');
        }else{
            $aeroportDepart_id = null;
            $aeroportArrivee_id = null;
            $volNew = new Vol();
            //si on ajoute depuis le planning
            if($request->query->has('idAvion')){
                $repoAvion = $em->getRepository('AirCorsicaXKPlanBundle:Avion');

                /** @var Vol $avionSel */
                $avionSel = $repoAvion->find($request->get('idAvion'));
                $volNew->setAvion($avionSel);
                $compagnieSel = $avionSel->getCompagnie();
                $volNew->setCompagnie($compagnieSel);
                $dateDuJour = $request->get('dateDuJour');
                $periodevolNew = new PeriodeDeVol();
                $periodevolNew->setDateDebut(new \DateTime($dateDuJour));
                $periodevolNew->setDateFin(new \DateTime($dateDuJour));
                $joursSemaine = $request->get('joursSemaine');
                $periodevolNew->setJoursDeValidite(explode(',',$joursSemaine));
                $volNew->setPeriodeDeVol($periodevolNew);
                $defaultAvion = $request->get('idAvion');
            }else{
                $volNew->setPeriodeDeVol(new PeriodeDeVol());
                $volNew->getPeriodeDeVol()->setDateDebut(null);
                $volNew->getPeriodeDeVol()->setDateFin(null);
            }

            $volNew->getPeriodeDeVol()->setAtterissage(null);
            $volNew->getPeriodeDeVol()->setDecollage(null);

        }

        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\VolType', $volNew,array(
            'attr' => array(
                'id'     => 'form_vol_edit',
                'idAvion' => $defaultAvion,
                'id_precedent' => $id_precedent,
                'periode_2' => $periode_2,
                'type' => $type,
                'dateDebut2' => $dateDebut2,
                'dateFin2' => $dateFin2,
            )
        ));

        if ( $request->get('aircorsica_xkplanbundle_vol') && array_key_exists('codesShareVol',$request->get('aircorsica_xkplanbundle_vol'))) {
            $listCodeShare = null == $request->get('aircorsica_xkplanbundle_vol')['codesShareVol'] ? array() : $request->get('aircorsica_xkplanbundle_vol')['codesShareVol'];
            $volNew->setCodesShareFromList($listCodeShare);
//            $volNew->setCodesShareFromList(array());
        }

        $form->handleRequest($request);
        if ( $request->get('aircorsica_xkplanbundle_vol') && $request->get('aircorsica_xkplanbundle_vol')['aeroport_depart'] && $request->get('aircorsica_xkplanbundle_vol')['aeroport_arrivee']) {
            $aeroportDepart = $em->find('AirCorsicaXKPlanBundle:Aeroport',$request->get('aircorsica_xkplanbundle_vol')['aeroport_depart']);
            $aeroportArrivee = $em->find('AirCorsicaXKPlanBundle:Aeroport',$request->get('aircorsica_xkplanbundle_vol')['aeroport_arrivee']);
            $repoLigne = $em->getRepository("AirCorsicaXKPlanBundle:Ligne");
            $ligne = $repoLigne->findLigneExistante($aeroportDepart,$aeroportArrivee);
            $volNew->setLigne($ligne);
        }
        $volNew->setTemplate($em->find('AirCorsicaXKPlanBundle:Template',$this->get('session')->get('template')));

        if ($form->isSubmitted() && $form->isValid()) {
            //On corrige les jours de validité
            $volNew->getPeriodeDeVol()->corrigerJourDeValidite(true);
            $volNew->getPeriodeDeVol()->corrigerDate(true);
            $volHistoriqueNew = VolHistorique::createFromVol($volNew);
            Vol::saveVolInHistorique($volHistoriqueNew,$em);
            $volNew->setVolHistoriqueParentable($volHistoriqueNew);
            $volHistoriqueNew->setVolHistorique($volNew);
            $em->persist($volNew);

            if( array_key_exists('periode2',$request->get('aircorsica_xkplanbundle_vol')) && 1 == $request->get('aircorsica_xkplanbundle_vol')['periode2']){
                $periode_2 = true;
                $volNew2 = clone $volNew;
                $volNew2->setId('');
                $periodeDevolNew2 = clone $volNew->getPeriodeDeVol();
                $periodeDevolNew2->setDateDebut(\DateTime::createFromFormat('j-m-Y',$request->get('aircorsica_xkplanbundle_vol')['periodeDeVol2']['dateDebut']));
                $periodeDevolNew2->setDateFin(\DateTime::createFromFormat('j-m-Y',$request->get('aircorsica_xkplanbundle_vol')['periodeDeVol2']['dateFin']));

                $periodeDevolNew2->setDateDebut($periodeDevolNew2->getDateDebut()->setTime(0,0,0));
                $periodeDevolNew2->setDateFin($periodeDevolNew2->getDateFin()->setTime(0,0,0));

                $periodeDevolNew2->setDecollage(\DateTime::createFromFormat(
                    'G:i',
                    $request->get('aircorsica_xkplanbundle_vol')['periodeDeVol']['decollage']
                ));

                $periodeDevolNew2->setAtterissage(\DateTime::createFromFormat(
                    'G:i',
                    $request->get('aircorsica_xkplanbundle_vol')['periodeDeVol']['atterissage']
                ));

                $periodeDevolNew2->setJoursDeValidite($request->get('aircorsica_xkplanbundle_vol')['periodeDeVol']['joursDeValidite']);
                $periodeDevolNew2->corrigerJourDeValidite(true);
                $periodeDevolNew2->corrigerDate(true);
                $volNew2->setPeriodeDeVol($periodeDevolNew2);
                $volHistoriqueNew2 = VolHistorique::createFromVol($volNew2);
                Vol::saveVolInHistorique($volHistoriqueNew2,$em);
                $volNew2->setVolHistoriqueParentable($volHistoriqueNew2);
                $volHistoriqueNew2->setVolHistorique($volNew2);
                $em->persist($volNew2);
            }
            $em->flush();

            if($request->get('poursuivre')){
                $response = new JsonResponse(array(
                    'volsuivant'=>$volNew->getId(),
                    'periode_2' => $periode_2,
                    'dateDebut2'=>$request->get('aircorsica_xkplanbundle_vol')['periodeDeVol2']['dateDebut'],
                    'dateFin2'=>$request->get('aircorsica_xkplanbundle_vol')['periodeDeVol2']['dateFin'],
                    ));
                return $response;
                //return $this->redirectToRoute('vol_new',array('volsuivant' => $volNew->getId() ));
            }
            $request->getSession()->getFlashBag()->add('success', 'Vol créé avec succés.');

            if($request->get('route_to_redirect')){
                return $this->redirectToRoute($request->get('route_to_redirect'));
            }else{
                return $this->redirect($_SERVER['HTTP_REFERER'],'302');
            }

        }

        return $this->render('AirCorsicaXKPlanBundle:Vol:create.html.twig', array(
            'vol' => $volNew,
            'form' => $form->createView(),
            'id_aeroport_arrivee' => $aeroportArrivee_id,
            'id_aeroport_depart' => $aeroportDepart_id,
            'periode_2' => $periode_2,
            'dateDebut2' => $dateDebut2,
            'dateFin2' => $dateFin2,
        ));
    }

    /**
     * Mise à jour du vol et de l'historique associé
     *
     * @param Vol $vol
     * @param Vol $volOrigine
     * @param $em
     *
     * @return void;
     */
//    public function updateVol(Vol $vol,Vol $volOrigine,$em )
//    {
//        $oPeriodeDeVol = $vol->getPeriodeDeVol();
//        /** @var Vol $volRoot */
//        $volHistoriqueRoot = $volOrigine->getVolHistoriqueParentable()->getRoot();
//        /** @var VolHistorique $volHistoriqueNew */
//        $volHistoriqueNew = VolHistorique::createFromVol($vol);
//        $volHistoriqueNew->setVolHistorique($vol);
//
//        if("send" == $volHistoriqueRoot->getPeriodeDeVol()->getEtat()){
//            if(!$volOrigine->getLigne()->compare($vol->getLigne())){
//                $vol->getVolHistoriqueParentable()->getPeriodeDeVol()->setEtat("cancel");
//                Vol::saveVolInHistorique($volHistoriqueNew,$em,$vol->getVolHistoriqueParentable()->getParent());
//                $volSave = $em->find('AirCorsicaXKPlanBundle:Vol',$vol->getId());
//                $volNew = clone $volSave;
//                $volNew->setId(null);
//                $volHistoriqueNew->setVolHistorique($volNew);
//                $volNew->setVolHistoriqueParentable($volHistoriqueNew);
//                $em->persist($volNew);
//                $em->flush();
//
//                return;
//                //return $this->redirectToRoute('vol_historique');
//            }
//
//            $periodeVol = $vol->getPeriodeDeVol();
//            $resultComparePeriode = $volOrigine->getPeriodeDeVol()->comparePeriodes($periodeVol);
//
//
//            foreach ($resultComparePeriode as $key => $infosPeriode) {
//                $oPeriodeDeVolClone = clone $oPeriodeDeVol;
//                $oPeriodeDeVolClone->setDates(current($infosPeriode));
//                $oPeriodeDeVolClone->setEtat(key($infosPeriode));
//                $volHistoriqueNew = VolHistorique::createFromVol($vol);
//                $volHistoriqueNew->setPeriodeDeVol($oPeriodeDeVolClone);
//
//                if ("periodeNew" == $key) {
//                    $aVolHistoriqueComplementaire = $volOrigine->getPeriodeDeVol()->updateJoursDeValidite(
//                        $volHistoriqueNew->getPeriodeDeVol(),
//                        $volOrigine,
//                        'volHistorique'
//                    );
//
//                    $vol->getPeriodeDeVol()->setDates(current($infosPeriode));
//                    $vol->getPeriodeDeVol()->setEtat(key($infosPeriode));
//
//                    if (null !== $aVolHistoriqueComplementaire) {
//                        $aVolComplementaire = $volOrigine->getPeriodeDeVol()->updateJoursDeValidite(
//                            $vol->getPeriodeDeVol(),
//                            $volOrigine,
//                            'vol'
//                        );
//                        foreach ($aVolHistoriqueComplementaire as $key => $volHistoriqueComplementJours) {
//                            Vol::saveVolInHistorique($volHistoriqueComplementJours,$em,$volOrigine->getVolHistoriqueParentable());
//                            $volComplementaire = $aVolComplementaire[$key];
//                            $volComplementaire->setVolHistoriqueParentable($volHistoriqueComplementJours);
//
//                            $volHistoriqueComplementJours->setVolHistorique($volComplementaire);
//
//                            $em->persist($volComplementaire);
//                        }
//                    }
//                } else {
//                    $oPeriodeDeVolClone2 = clone $oPeriodeDeVol;
//                    $oPeriodeDeVolClone2->setDates(current($infosPeriode));
//                    $oPeriodeDeVolClone2->setEtat(key($infosPeriode));
//                    $newVolPeriode = clone $vol;
//                    $newVolPeriode->setId(null);
//                    $newVolPeriode->setPeriodeDeVol($oPeriodeDeVolClone2);
//                    $newVolPeriode->setVolHistoriqueParentable($volHistoriqueNew);
//                    Vol::saveVolInHistorique($volHistoriqueNew,$em,$volOrigine->getVolHistoriqueParentable());
//                    $volHistoriqueNew->setVolHistorique($newVolPeriode);
//
//                    $em->persist($newVolPeriode);
//
//                    continue;
//                }
//
//                $volHistoriqueNew->setVolHistorique($vol);
//
//                Vol::saveVolInHistorique($volHistoriqueNew,$em,$volOrigine->getVolHistoriqueParentable());
//                $vol->setVolHistoriqueParentable($volHistoriqueNew);
//            }
//        }else{
//            $vol->getVolHistoriqueParentable()->updateFromVol($vol);
//        }
//
//        //$this->getDoctrine()->getManager()->flush();
//
//        //return $this->redirectToRoute('vol_historique');
//        return;
//    }


    /**
     * Displays a form to edit an existing vol entity.
     *
     * @param Request $request
     * @param Vol $vol
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, Vol $vol)
    {
        $em = $this->getDoctrine()->getManager();
        $volOrigine = clone $vol;

        if ( $request->get('aircorsica_xkplanbundle_vol') && array_key_exists('codesShareVol',$request->get('aircorsica_xkplanbundle_vol'))) {
            $listCodeShare = null == $request->get('aircorsica_xkplanbundle_vol')['codesShareVol'] ? array() : $request->get('aircorsica_xkplanbundle_vol')['codesShareVol'];
            $vol->setCodesShareFromList($listCodeShare);
        }

        $deleteForm = $this->createDeleteForm($vol);
        $editForm = $this->createForm('AirCorsica\XKPlanBundle\Form\VolType', $vol,array(
            'action' => $this->generateUrl('vol_edit',array('id'=>$vol->getId())),
            'method' => 'POST',
            'attr' => array(
                'id'     => 'form_vol_edit',
                'idAvion' => $vol->getAvion()->getId(),
            ),
        ));

        //En edition le type de vol est disabled si le vol est connu donc il n'est pas posté
        //readonly n'a pas l'air de marcher avec select2
        if("send" == $volOrigine->getPeriodeDeVol()->getEtat())
        {
            $parameters = $request->request->all();
            $parameters['aircorsica_xkplanbundle_vol']['typeDeVol'] = $volOrigine->getTypeDeVol()->getId();
            $request->request->add($parameters);
        }


        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $aeroportDepart = $em->find('AirCorsicaXKPlanBundle:Aeroport',$request->get('aircorsica_xkplanbundle_vol')['aeroport_depart']);
            $aeroportArrivee = $em->find('AirCorsicaXKPlanBundle:Aeroport',$request->get('aircorsica_xkplanbundle_vol')['aeroport_arrivee']);
            $repoLigne = $em->getRepository("AirCorsicaXKPlanBundle:Ligne");
            $ligne = $repoLigne->findLigneExistante($aeroportDepart,$aeroportArrivee);
            $vol->setLigne($ligne);

            $vol->updateVol($volOrigine,$em );
            $this->getDoctrine()->getManager()->flush();

            //message de succés
            $request->getSession()->getFlashBag()->add('success', 'Vol modifié avec succés.');

            return $this->redirect($_SERVER['HTTP_REFERER'],'302');
            //return $this->redirectToRoute('vol_historique');
        }

        return $this->render('AirCorsicaXKPlanBundle:Vol:create.html.twig', array(
            'vol' => $vol,
            'edition' => true,
            'form' => $editForm->createView(),
            'id_aeroport_arrivee' => $vol->getLigne()->getAeroportArrivee()->getId(),
            'id_aeroport_depart' => $vol->getLigne()->getAeroportDepart()->getId(),
            'delete_form' => $deleteForm->createView(),
            'volHistoriqueRoot' => $vol->getVolHistoriqueParentable()->getRoot(),
        ));
    }

    /**
     * Finds and displays a vol entity.
     *
     */
    public function showAction(Vol $vol)
    {
        $deleteForm = $this->createDeleteForm($vol);

        return $this->render('AirCorsicaXKPlanBundle:Vol:show.html.twig', array(
            'vol' => $vol,
            'delete_form' => $deleteForm->createView(),
        ));
    }


    /**
     * delestage d'un vol
     * @param Vol $vol
     * @param string $date => format  "aaaa-mm-dd"
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function delestagePonctuelAction(Request $request, Vol $vol, $date)
    {
        $request->request->add(array("choix_ponctuel" => "choix_delestage"));

        $jourDeleste = new \DateTime($date);

        $request->request->add(array("debut_filtre" => $jourDeleste->format("Y-m-d")));
        $request->request->add(array("fin_filtre" => $jourDeleste->format("Y-m-d")));
        $request->request->add(array("ids_vol" => $vol->getId()));

        $jouramodifier = intval($jourDeleste->format('N'));
        $jour_val = array(1=>"-",2=>"-",3=>"-",4=>"-",5=>"-",6=>"-",7=>"-");
        $jour_val[$jouramodifier] = $jouramodifier;
        $request->request->add(array("jour_val" => implode("_",$jour_val)));

        return $this->updatemasseAction($request);

        //----------------------------
        //on récupére l'entity manager
        //----------------------------
//        $em = $this->getDoctrine()->getManager();

        //--------------------------------------------------------------------
        //opération de délestage ponctuelle pour le jour $date sur le vol $vol
        //--------------------------------------------------------------------
//        $jourDeleste = new \DateTime($date);
//        $vol->delestagePonctuel($jourDeleste,$em);

        //--------------------------------
        // retour sur la page planning vol
        //--------------------------------
//        return $this->redirectToRoute('planningvol_show');
    }

    /**
     * delestage d'une période
     * @param Vol $vol
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function delestagePeriodeAction(Request $request, Vol $vol)
    {
//        $em = $this->getDoctrine()->getManager();
//        $vol->delestagePeriode($em);
//        $em->flush();
//        return $this->redirectToRoute('planningvol_show');

        $request->request->add(array("choix_ponctuel" => "choix_delestage"));

//        $jourDeleste = new \DateTime($date);

        $periodeDeVolDeleste = $vol->getPeriodeDeVol();

        $request->request->add(array("debut_filtre" => $periodeDeVolDeleste->getDateDebut()->format("Y-m-d")));
        $request->request->add(array("fin_filtre" => $periodeDeVolDeleste->getDateFin()->format("Y-m-d")));
        $request->request->add(array("ids_vol" => $vol->getId()));

//        $jouramodifier = intval($jourDeleste->format('N'));
//        $jour_val = array(1=>"-",2=>"-",3=>"-",4=>"-",5=>"-",6=>"-",7=>"-");
//        $jour_val[$jouramodifier] = $jouramodifier;
        $request->request->add(array("jour_val" => implode("_",$periodeDeVolDeleste->getJoursDeValidite())));

        return $this->updatemasseAction($request);
    }

    /**
     * Deletes a vol entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, Vol $vol)
    {
        $form = $this->createDeleteForm($vol);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($vol);
            $em->flush($vol);
        }

        return $this->redirectToRoute('vol_index');
    }

    /**
     * annule l'historisation des modifications d'un vol en AJAX
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function ajaxcancelhistoriqueAction(VolHistorique $volHistorique){
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AirCorsicaXKPlanBundle:VolHistorique');
        $childrens = $repo->getChildren($volHistorique);
        /** @var Vol $vol */
        $vol = $em->find("AirCorsicaXKPlanBundle:Vol",$volHistorique->getVolHistorique());

        /** @var VolHistorique $children */
        foreach ($childrens as $children){
            /** @var Vol $volRelation */
            $volRelation = $children->getVolHistorique();
            if($vol->getId() != $volRelation->getId()){
                $em->remove($volRelation);
            }else{
                $volRelation->backUpFromVolHistorique($volHistorique);

                $em->persist($volRelation);
            }
            $repo->removeFromTree($children);

        }

//        if(!$repo->recover()){
//            throw new \Exception("Error de recover tree doctrine");
//        }

        $em->flush();
        $response = new JsonResponse();

        return $response;
    }

    /**
     * @param int $id id du vol
     * @return JsonResponse
     */
    public function ajaxGetCodesSharesVolAction($id){
        $em = $this->getDoctrine()->getManager();
        $vol = $em->find('AirCorsicaXKPlanBundle:Vol',$id);
        $response = new JsonResponse();
        $reports = $vol->getCodesShareVol()->getValues();
        $response->setData($reports);
        return $response;
    }

    /**
     * @param string $libelle numero du vol
     * @return JsonResponse
     */
    public function ajaxGetCodesSharesPrechargesAction($libelle){
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AirCorsicaXKPlanBundle:CodeSharePrecharge');
        $data = $repo->getCodesPrechargesFromLibelle($libelle);
        $response = new JsonResponse();
        $response->setData($data);
        return $response;
    }

    /**
     * @param  Aeroport $aeroportDepart
     * @param  Aeroport $aeroportArrivee
     * @param  Avion $avion
     * @param \DateTime $dateDebut
     * @param \DateTime $dateFin
     * @return JsonResponse | null
     *
     * @ParamConverter("aeroportDepart", options={"mapping": {"aeroport_depart": "id"}})
     * @ParamConverter("aeroportArrivee", options={"mapping": {"aeroport_arrivee": "id"}})
     * @ParamConverter("typeAvion", options={"mapping": {"type_avion": "id"}})
     * @ParamConverter("dateDebut", options={"format": "d-m-Y"})
     * @ParamConverter("dateFin", options={"format": "d-m-Y"})
     *
     */
    public function ajaxGetTempdsDeVolAction(Aeroport $aeroportDepart,Aeroport $aeroportArrivee,Avion $avion, \DateTime $dateDebut, \DateTime $dateFin){
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AirCorsicaXKPlanBundle:TempsDeVol');
        $typeAvion = $avion->getTypeAvion();

        /** @var TempsDeVol $tdv */
        $tdv = $repo->findTdvForVol($aeroportDepart, $aeroportArrivee, $typeAvion, $dateDebut, $dateFin);
        if($tdv){
            $response = new JsonResponse(array('duree'=>$tdv->getDuree()));

        }else{
           $response = new JsonResponse(array('duree'=>null));
        }
        return $response;
    }



    /**
     * @param int $aeroport_depart id aeroport depart
     * @param int $aeroport_arrivee id aeroport arrivee
     * @return JsonResponse
     */
    public function ajaxGetIdLigneAction($aeroport_depart,$aeroport_arrivee){
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AirCorsicaXKPlanBundle:Ligne');
        $repoAeroport = $em->getRepository('AirCorsicaXKPlanBundle:Aeroport');

        $oAeroport_depart = $repoAeroport->find($aeroport_depart);
        $oAeroport_arrivee= $repoAeroport->find($aeroport_arrivee);

        $ligne = $repo->findLigneExistante($oAeroport_depart,$oAeroport_arrivee);
        $response = new JsonResponse();
        if($ligne instanceof Ligne){
            $response->setData($ligne->getId());
        }else{
            $response->setData(null);
        }

        return $response;
    }




    /**
     * Creates a form to delete a vol entity.
     *
     * @param Vol $vol The vol entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Vol $vol)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('vol_delete', array('id' => $vol->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    public function timetabletocsvAction(Request $request){

        $aaVols = $this->timetableAction($request, 'csv');

        $response = new StreamedResponse();
        $response->setCallback(function() use($aaVols){

            $handle = fopen('php://output', 'w+');
            // Nom des colonnes du CSV
//            fputcsv($handle, array(utf8_decode("Numéro du Vol"),
//                utf8_decode("Aéroport d'arrivée"),
//                utf8_decode("Aéroport de départ"),
//                utf8_decode("Compagnie"),
//                utf8_decode("Date Début"),
//                "Date Fin",
//                utf8_decode("Heure décollage"),
//                utf8_decode("Heure attérissage"),
//                "Lundi",
//                "Mardi",
//                "Mercredi",
//                "Jeudi",
//                "Vendredi",
//                "Samedi",
//                "Dimanche",
//                "Type d'avion",
//                "Type de vol",
//                utf8_decode("Sièges"),
//                "Codes shares",
//            ),';');

            //Champs
            /** @var Vol $vol */
            foreach ($aaVols as $aVols)
            {
                foreach ($aVols as $vol){
                    $lundi = "";
                    if(in_array(1,$vol->getPeriodeDeVol()->getJoursDeValidite())){$lundi=1;}
                    $mardi = "";
                    if(in_array(2,$vol->getPeriodeDeVol()->getJoursDeValidite())){$mardi=2;}
                    $mercredi = "";
                    if(in_array(3,$vol->getPeriodeDeVol()->getJoursDeValidite())){$mercredi=3;}
                    $jeudi = "";
                    if(in_array(4,$vol->getPeriodeDeVol()->getJoursDeValidite())){$jeudi=4;}
                    $vendredi = "";
                    if(in_array(5,$vol->getPeriodeDeVol()->getJoursDeValidite())){$vendredi=5;}
                    $samedi = "";
                    if(in_array(6,$vol->getPeriodeDeVol()->getJoursDeValidite())){$samedi=6;}
                    $dimanche = "";
                    if(in_array(7,$vol->getPeriodeDeVol()->getJoursDeValidite())){$dimanche=7;}

                    if('Aucun' != $vol->getCodesShareVolToString(',')){
                        $valueCS = $vol->getCodesShareVolToString(',');
                    }else{
                        $valueCS = '';
                    }


                    fputcsv($handle,array($vol->getNumero(),
                        $vol->getLigne()->getAeroportDepart()->getCodeIATA(),
                        $vol->getLigne()->getAeroportArrivee()->getCodeIATA(),
                        $vol->getCompagnie()->getCodeIATA(),
                        $vol->getPeriodeDeVol()->getDateDebut()->format('d/m/Y'),
                        $vol->getPeriodeDeVol()->getDateFin()->format('d/m/Y'),
                        $vol->getPeriodeDeVol()->getDecollage()->format('H:i'),
                        $vol->getPeriodeDeVol()->getAtterissage()->format('H:i'),
                        $lundi,
                        $mardi,
                        $mercredi,
                        $jeudi,
                        $vendredi,
                        $samedi,
                        $dimanche,
                        $vol->getAvion()->getTypeAvion()->getCodeIATA(),
                        utf8_decode($vol->getTypeDeVol()->getCodeType()),
                        $vol->getAvion()->getTypeAvion()->getCapaciteSiege(),
                        $valueCS,
                        $vol->getPeriodeDeVol()->getEtat()
                    ),';');
                }
            }
            fclose($handle);
        }
        );
       $response->setStatusCode(200);
       $now = new \DateTime();
       $response->headers->set('Content-Type', 'text/csv; charset=iso-8859-1');
       $response->headers->set('Content-Disposition','attachment; filename="export_'.$now->format('dmY').'.csv"');

       return $response;
    }

    public function returnPDFResponseFromHTML($html,$sujet,$filename){
        //set_time_limit(30); uncomment this line according to your needs
        // If you are not in a controller, retrieve of some way the service container and then retrieve it
        //$pdf = $this->container->get("white_october.tcpdf")->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        //if you are in a controlller use :
        $pdf = $this->get("white_october.tcpdf")->create('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetAuthor('Air Corsica');
        $pdf->SetTitle(('XKPLAN'));
        $pdf->SetSubject($sujet);
        $pdf->setFontSubsetting(true);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->SetFont('helvetica', '', 11, '', true);
        //$pdf->SetMargins(20,20,40, true);
        $pdf->AddPage();

        $filename = $filename;

        $pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
        $pdf->Output($filename.".pdf",'I'); // This will output the PDF as a response directly
    }

    public function timetabletopdfAction(Request $request){

        $html = $this->timetableAction($request,'pdf');
        $sujet = 'Time Table';
        $filename = 'XKPLAN_timetable';
        $this->returnPDFResponseFromHTML($html,$sujet,$filename);
    }

    public function verificateurcoherencetopdfAction(Request $request)
    {
        $html = $this->verificateurcoherenceAction($request,'pdf');
        $saison = "";
        $sujet = "Cohérence des vols";
        $filename = "coherencedesvols_".$saison;
        $this->returnPDFResponseFromHTML($html,$sujet,$filename);
    }

    private function sendEmail($objet,$from,$to,$view,$paramsToView,$aAttachement = array()){
        $message = \Swift_Message::newInstance()
            ->setSubject($objet)
            ->setFrom($from)
            ->setTo($to);
            if($this->container->get( 'kernel' )->getEnvironment() == "prod") {
                $message->setBcc($from);
            }
            $message->setBody(
                $this->renderView(
                // app/Resources/views/Emails/registration.html.twig
                    $view,
                    array('aParam' => $paramsToView)
                ),
                'text/plain'
            )
            /*
             * If you also want to include a plaintext version of the message
            ->addPart(
                $this->renderView(
                    'Emails/registration.txt.twig',
                    array('name' => $name)
                ),
                'text/plain'
            )
            */
        ;

        if(sizeof($aAttachement)){
            foreach ($aAttachement as $extension=>$filename){
                $message->attach(Swift_Attachment::fromPath($filename));
            }
        }
        $this->get('mailer')->send($message);
    }

    private function getEtatApresMaj(Vol $volOrigine, Vol $volModifie){
        $volhistoriqueachercher = $volModifie->getVolHistoriqueParentable();

        if("send" == $volhistoriqueachercher->getPeriodeDeVol()->getEtat()){
            $dernierVolDeHistoriqueAEtatConnu = $volhistoriqueachercher;
        }else{
            while(true){
                $parent = $volhistoriqueachercher->getParent();

                if($parent==null){
                    if("send" == $volhistoriqueachercher->getPeriodeDeVol()->getEtat()){
                        $dernierVolDeHistoriqueAEtatConnu = $volhistoriqueachercher;
                    }else{
                        $dernierVolDeHistoriqueAEtatConnu = null;
                    }

                    break;
                }

                $etat = $parent->getPeriodeDeVol()->getEtat();
                if($etat=='send'){
                    $dernierVolDeHistoriqueAEtatConnu = $parent;
                    break;
                }
                $volhistoriqueachercher = $parent;
            }
        }


        //$dernierVolDeHistoriqueAEtatConnu est send
        if($dernierVolDeHistoriqueAEtatConnu && true === $volModifie->isVolChangeStateWithLastVolSend($dernierVolDeHistoriqueAEtatConnu,$volOrigine)){
            $volModifie->getPeriodeDeVol()->setEtat('send');
            $etat = 'send';
        }
        elseif(true === $volModifie->isVolChangeState($volOrigine)){
            $volModifie->getPeriodeDeVol()->setEtat('pendingSend');
            $etat = 'pendingSend';
        }else{
            $etat = $volModifie->getPeriodeDeVol()->getEtat();
        }

        return $etat;
    }
}
