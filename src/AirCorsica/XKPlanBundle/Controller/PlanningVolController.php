<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\PlanningVolPDFPrinter;
use AirCorsica\XKPlanBundle\Entity\VolHistorique;
use AirCorsica\XKPlanBundle\Entity\Vol;
use AirCorsica\XKPlanBundle\Entity\PeriodeDeVol;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * PlanningVol controller.
 *
 */
class PlanningVolController extends Controller
{

    private $monPdf;

    public function getmodalnewatterrisagetimeandtempdevolAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $result="";

        //la date de la modification ponctuelle
        $jourString = $request->get('dateModifPonctuelle');
        $dateJour = new \DateTime($jourString);

        //vol à modifier
        $idVolModifPonctuelle = $request->get('idVol');
        $volModifPonctuelle = clone $em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($idVolModifPonctuelle);

        //nouvel avion
        $idAvionModifPonctuelle = $request->get('idAvion');
        $avionModifPonctuelle = clone $em->getRepository('AirCorsicaXKPlanBundle:Avion')->find($idAvionModifPonctuelle);

        //decollage
        $decollageString = $request->get('decollage');

        //atterissage
        $atterissageString = $request->get('atterissage');

        //recherche de la nouvelle durée du vol
        $nouvelleDureeDuVol = $this->getPlaneVolDurationForAVolForAPeriod($volModifPonctuelle,$avionModifPonctuelle,$dateJour,$dateJour,$em);
        $dureeheures = floor($nouvelleDureeDuVol / 60);
        $dureeminutes = $nouvelleDureeDuVol % 60;

        if($decollageString != ""){

            //on calcul la nouvelle heure d'atterissage
            $nouvelleheureatterissage = \DateTime::createFromFormat('G:i',$decollageString);
            $nouvelleheureatterissage->modify('+' . $nouvelleDureeDuVol . ' minutes');

            $result = '{ "dureevol": "'.$nouvelleDureeDuVol.'","dureeminutes": "'.$dureeminutes.'","dureeheures": "'.$dureeheures.'","decollage": "'.$decollageString.'","atterissage": "'.$nouvelleheureatterissage->format('G:i').'"}';

        }

        if($atterissageString != ""){

            //on calcul la nouvelle heure de décollage
            $nouvelleheuredecollage = \DateTime::createFromFormat('G:i',$atterissageString);
            $nouvelleheuredecollage->modify('-' . $nouvelleDureeDuVol . ' minutes');

            $result = '{ "dureevol": "'.$nouvelleDureeDuVol.'","dureeminutes": "'.$dureeminutes.'","dureeheures": "'.$dureeheures.'","decollage": "'.$nouvelleheuredecollage->format('G:i').'","atterissage": "'.$atterissageString.'"}';
        }

//        //on calcul la nouvelle heure d'atterissage
//        $nouvelleheureatterissage = \DateTime::createFromFormat('G:i',$decollageString);
//        $nouvelleheureatterissage->modify('+' . $nouvelleDureeDuVol . ' minutes');
//
//        $result = '{ "dureevol": "'.$nouvelleDureeDuVol.'","dureeminutes": "'.$dureeminutes.'","dureeheures": "'.$dureeheures.'","atterissage": "'.$nouvelleheureatterissage->format('G:i').'"}';

        return new Response(
            json_encode($result)
        );

    }

    public function populatevolinvisualisationinfosmodalAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        //vol à affciher
        $idVol = $request->get('idVol');
        $monvol = clone $em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($idVol);

        return $this->render('AirCorsicaXKPlanBundle:PlanningVol:createModalBodyAfficheVolContent.html.twig',
            array(
                'aeroport_arrivee' => $monvol->getLigne()->getAeroportArrivee()->getCodeIATA(),
                'aeroport_depart' => $monvol->getLigne()->getAeroportDepart()->getCodeIATA(),
                'numero_vol' => $monvol->getNumero(),
                'array_codeshare' => explode('_',$monvol->getCodesShareVolToString()),
                'type_vol' => $monvol->getTypeDeVol()->getCodeType()." ".$monvol->getTypeDeVol()->getCodeService(),
                'compagnie' => $monvol->getAvion()->getCompagnie()->getCodeIATA()." : ".$monvol->getAvion()->getCompagnie()->getNom(),
                'affretement' => $monvol->getAffretement()->getNom(),
                'nom_avion' => $monvol->getAvion()->getNom(),
                'tempsdemitour_avionorigine' => $monvol->getAvion()->getTypeAvion()->getTempsDemiTour(),
                'periodevalidite_debut' => $monvol->getPeriodeDeVol()->getDateDebut()->format('d-m-Y'),
                'periodevalidite_fin' => $monvol->getPeriodeDeVol()->getDateFin()->format('d-m-Y'),
                'tempsdevol' => $monvol->getTempsDeVol(),
                'decollage' => $monvol->getPeriodeDeVol()->getDecollage()->format('H:i'),
                'atterissage' => $monvol->getPeriodeDeVol()->getAtterissage()->format('H:i'),
                'jourdevalidite' => $monvol->getPeriodeDeVol()->getJoursDeValidite(),
            )
        );

    }

    public function populatevolinmodifponctuellemodalAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        //la date de la modification ponctuelle
        $jourStringUS = $request->get('dateModifPonctuelle'); //par exemple: 2016-05-20
        $temp = explode('-',$jourStringUS);
        $jourString = $temp[2].'-'.$temp[1].'-'.$temp[0];
        $dateJour = new \DateTime($jourString);

        //vol à modifier
        $idVolModifPonctuelle = $request->get('idVol');
        $volModifPonctuelle = clone $em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($idVolModifPonctuelle);

        //le tableau d'avions
        $acustomorderavions = array();
        //on cherche dabords les avions AIRCORSICA
        $compagnie = $em->getRepository('AirCorsicaXKPlanBundle:Compagnie')->find('1'); //AIR CORSICA à pour id 1 dans la BDB XKPlan
        $criteria = array('compagnie'=>$compagnie);
        $order = array('ordre' => 'ASC');
        $aAvion = $em->getRepository('AirCorsicaXKPlanBundle:Avion')->findBy($criteria, $order);
        foreach ($aAvion as $unAvion){
            array_push($acustomorderavions,array("id"=>$unAvion->getId(),"nom"=>$unAvion->getNom(),"tmpsdemitour"=>$unAvion->getTypeAvion()->getTempsDemiTour(),"idcompagnie"=>$unAvion->getCompagnie()->getId()));
        }
        //on rajoute les autres avions
        $aAvion = $em->getRepository('AirCorsicaXKPlanBundle:Avion')->findNotCompagnie('1');
        foreach ($aAvion as $unAvion){
            array_push($acustomorderavions,array("id"=>$unAvion->getId(),"nom"=>$unAvion->getNom(),"tmpsdemitour"=>$unAvion->getTypeAvion()->getTempsDemiTour(),"idcompagnie"=>$unAvion->getCompagnie()->getId()));
        }

        //le tableau temps de vol
        $atempsdevol = array();
        $volDuration = intVal($this->getPlaneVolDurationForAVolForAPeriod($volModifPonctuelle,$volModifPonctuelle->getAvion(),$dateJour,$dateJour,$em));
        $atempsdevol['heures'] = floor($volDuration / 60);
        $atempsdevol['minutes'] = $volDuration % 60;
        $atempsdevol['original'] = $volDuration;

        //jour de validité pour ce jour
        $joursDeValidites = ["0" => "-", "1" => "-", "2" => "-", "3" => "-", "4" => "-", "5" => "-", "6" => "-"];
        $joursDeValidites[$dateJour->format('N') - 1] = $dateJour->format('N');

        return $this->render('AirCorsicaXKPlanBundle:PlanningVol:createModalBodyModifPonctuelleContent.html.twig',
            array(
                'id_vol' => $volModifPonctuelle->getId(),
                'aeroport_arrivee' => $volModifPonctuelle->getLigne()->getAeroportArrivee()->getCodeIATA(),
                'aeroport_depart' => $volModifPonctuelle->getLigne()->getAeroportDepart()->getCodeIATA(),
                'numero_vol' => $volModifPonctuelle->getNumero(),
                'array_codeshare' => explode('_',$volModifPonctuelle->getCodesShareVolToString()),
                'type_vol' => $volModifPonctuelle->getTypeDeVol()->getCodeType()." ".$volModifPonctuelle->getTypeDeVol()->getCodeService(),
                'compagnie' => $volModifPonctuelle->getAvion()->getCompagnie()->getCodeIATA()." : ".$volModifPonctuelle->getAvion()->getCompagnie()->getNom(),
                'affretement' => $volModifPonctuelle->getAffretement()->getNom(),
                'id_avionorigine' => $volModifPonctuelle->getAvion()->getId(),
                'array_avions' => $acustomorderavions,
                'tempsdemitour_avionorigine' => $volModifPonctuelle->getAvion()->getTypeAvion()->getTempsDemiTour(),
                'periodevalidite_debut' => $jourString, //jour de la modif ponctuelle
                'periodevalidite_fin' => $jourString,  //jour de la modif ponctuelle
                'tempsdevol' => $atempsdevol,
                'decollage' => $volModifPonctuelle->getPeriodeDeVol()->getDecollage()->format('H:i'),
                'atterissage' => $volModifPonctuelle->getPeriodeDeVol()->getAtterissage()->format('H:i'),
                'jourdevalidite' => $joursDeValidites,
            )
        );

    }

    /**
     * Affichage du planning.
     *
     */
    public function showAction(Request $request)
    {

         $em = $this->getDoctrine()->getManager();

        //récupération des variables de sessions utilisés dans le panning de vol
        $session = $request->getSession();

        // au cas ou ne soit pas encore allé sur le planning de vol
        if(!$session->has('pv_interactionsouris')){
            $session->set('pv_interactionsouris',false);
        }

        if(!$session->has('pv_typedemodification')){
            $session->set('pv_typedemodification',0);
        }

        if(!$session->has('pv_periodecustom')){
            $session->set('pv_periodecustom',array('debut'=>'','fin'=>''));
        }

        if(!$session->has('pv_typedeplanning')){
            $session->set('pv_typedeplanning',0);
        }

        if(!$session->has('pv_aselectionvols')){
            $session->set('pv_aselectionvols',array());
        }

        if(!$session->has('pv_datepencouredition')){
            $datedujour = date_create();
            $session->set('pv_datepencouredition',$datedujour->format('d-m-Y'));
        }

        if(!$session->has('pv_zoominterface')){
            $session->set('pv_zoominterface',-1);
        }

        if(!$session->has('pv_idsaisonselected')){
            $session->set('pv_idsaisonselected','');
        }

        $session->set('pv_timelinesechellevisualisation',1);

        $em = $this->getDoctrine()->getManager();
        //$templates = $em->getRepository('AirCorsicaXKPlanBundle:Template')->findAll();
        //On cherche uniquement les templates actifs (cad inactif = 0)
        $criteria = array('inactif'=>'0');
        $templates = $em->getRepository('AirCorsicaXKPlanBundle:Template')->findBy($criteria);

        if($session->has('template')){//si l'attribut template existe dans la variable de session, on le récupére
                $templateCourant = $session->get('template');
        }else{//on récupére le 1er template retourné par la base et on l'enregistre en session
            $templateCourant = $templates[0];
            $session->set('template',$templateCourant->getId());
        }

        $saisons = $em->getRepository('AirCorsicaXKPlanBundle:Saison')->findAll();
        $asaisons = array();
        $i=0;
        foreach ($saisons as $uneSaison){

            if(sizeof($uneSaison->getPeriodesSaison()) != 0) { //on enregistre uniquement les saisons avec une/des périodes saisons

                $asaisons[$i] = array();
                $j = 0;
                $aperiodes = array();
                $asaisons[$i]['nom'] = $uneSaison->getNom();
                $periodesdelasaison = $uneSaison->getPeriodesSaison();

                foreach ($periodesdelasaison as $unePeriode) {
                    $aperiodes[$j] = array();
                    $aperiodes[$j]['id'] = $unePeriode->getId(); //PB id = null (verifier entity)
                    $aperiodes[$j]['nom'] = $unePeriode->getNom();
                    $aperiodes[$j]['datedebut'] = $unePeriode->getDateDebut();
                    $aperiodes[$j]['datefin'] = $unePeriode->getDateFin();

                    if($session -> get('pv_idsaisonselected') == '') {

                        $dateDuPlanningVol = date_create_from_format('d-m-Y', $session->get('pv_datepencouredition')); //est-ce que le jour d'edition du planning fais partie d'une période
                        if ((intval(date_diff($unePeriode->getDateDebut(), $dateDuPlanningVol)->format('%R%a')) >= 0) && (intval(date_diff($dateDuPlanningVol, $unePeriode->getDateFin())->format('%R%a')) >= 0)) {
                            $asaisons[$i]['aperiodes'][$j]['selected'] = 1;
                        } else {
                            $asaisons[$i]['aperiodes'][$j]['selected'] = 0;
                        }

                    }else if( $unePeriode->getId() == $session -> get('pv_idsaisonselected')){

                        $asaisons[$i]['aperiodes'][$j]['selected'] = 1;

                    } else{

                        $asaisons[$i]['aperiodes'][$j]['selected'] = 0;

                    }

                    $asaisons[$i]['aperiodes'][$j]['data'] = $aperiodes[$j];

                    $j++;
                }

                $i++;

            }

        }

        $numerodujourcourant = date('N', strtotime($session->get('pv_datepencouredition')))-1;

        //récupération des avions
        $acustomorderavions = array();
        $aircorsicaavions = "";
        //on cherche dabords les avions AIRCORSICA
        $compagnie = $em->getRepository('AirCorsicaXKPlanBundle:Compagnie')->find('1'); //AIR CORSICA à pour id 1 dans la BDB XKPlan
        $criteria = array('compagnie'=>$compagnie);
        $order = array('ordre' => 'ASC');
        $aAvion = $em->getRepository('AirCorsicaXKPlanBundle:Avion')->findBy($criteria, $order);
        foreach ($aAvion as $unAvion){
            array_push($acustomorderavions,array("id"=>$unAvion->getId(),"title"=>$unAvion->getNom()));
            $aircorsicaavions.=$unAvion->getId()."_";
        }
        //on rajoute les autres avions
        $aAvion = $em->getRepository('AirCorsicaXKPlanBundle:Avion')->findNotCompagnie('1');
        foreach ($aAvion as $unAvion){
            array_push($acustomorderavions,array("id"=>$unAvion->getId(),"title"=>$unAvion->getNom()));
        }

        if($numerodujourcourant!=0){
            $lundisemainedujourcourant = date('d-m-Y', strtotime('last monday', strtotime($session->get('pv_datepencouredition'))));
        }else{
            $lundisemainedujourcourant = date('d-m-Y',strtotime($session->get('pv_datepencouredition')));
        }
        if($numerodujourcourant!=6) {
            $dimanchesemainedujourcourant = date('d-m-Y', strtotime('next sunday', strtotime($session->get('pv_datepencouredition'))));
        }else{
            $dimanchesemainedujourcourant = date('d-m-Y',strtotime($session->get('pv_datepencouredition')));
        }

        return $this->render('AirCorsicaXKPlanBundle:PlanningVol:show.html.twig', array(
            'avionsaircorsica' => substr($aircorsicaavions, 0, -1),
            'aavions' => $acustomorderavions,
            'numerodujourcourant' => $numerodujourcourant,
            'atemplates' => $templates,
            'asaisons' => $asaisons,
            'templateCourantId' => $session->get('template'),
            'interactionsourisplanning' => ($session->get('pv_interactionsouris')) ? 'true' : 'false',
            'typedemodification' => ($session -> get('pv_typedemodification')),
            'periodecustom' => $session->get('pv_periodecustom'),
            'typedeplanning' => ($session -> get('pv_typedeplanning')),
            'aselectionvolsplanning' => json_encode($session->get('pv_aselectionvols')),
            'dateplanningencouredition' => $session->get('pv_datepencouredition'),
            'lundiplanninghebdomadairecourant' => $lundisemainedujourcourant,
            'dimancheplanninghebdomadairecourant' => $dimanchesemainedujourcourant,
            'zoominterface' => strval($session->get('pv_zoominterface')),
            'idSaisonSelected' => $session -> get('pv_idsaisonselected'),
            /*'timelinesechellevisualisation' => strval($session->get('pv_timelinesechellevisualisation')),*/
        ));
    }

    /**
     * Génére un padf destiné à l'impression en fonction des paramétres GET transmis par le javascript
     *
     * @param Request $request
     *
     */
    public function printPDFPlanningVolsAction(Request $request){

        //on récupére les paramètres GET
        $dateDebutString = $request->get('dateDebut'); //par exemple: 2016-05-20
        $dateDebut = date_create_from_format('Y-m-d', $dateDebutString);//2016-05-20
        $dateDeFinString = $request->get('dateFin');
        $dateDeFin = date_create_from_format('Y-m-d', $dateDeFinString); //2016-05-21
        $aJoursDeLaSemaine = explode("_",$request->get('aJoursDeLaSemaine')); //par exemple: -_-_2_-_-_4_5_- //0->lundi, 1 ->mardi, 2->mercredi, 3->jeudi, 4->vebdredi, 5->samedi, 6->dimanche
        $aIdAvionsAImprimer = explode("_",$request->get('aIdAvionsAImprimer')); //par exemple: 5_478_145_87
        $optionImpressionAvions = $request->get('optionImpressionAvions');

        switch($request->get('reperesdurees')){
            case 'true':
                $reperesdurees = true;
                break;
            case 'false':
                $reperesdurees = false;
                break;
        }

        switch($request->get('overflowVolsCourts')){
            case 'true':
                $overflowTexteVolsCourts = true;
                break;
            case 'false':
                $overflowTexteVolsCourts = false;
                break;
        }

        $dureemaxivolscourts = $request->get('dureemaxivolscourts');

        switch($request->get('enteteaircorsica')){
            case 'true':
                $enteteCompagnie = true;
                break;
            case 'false':
                $enteteCompagnie = false;
                break;
        }

        switch($request->get('entetejoursemaine')){
            case 'true':
                $enteteJourSemaine = true;
                break;
            case 'false':
                $enteteJourSemaine = false;
                break;
        }

        switch($request->get('entetenumerosemaine')){
            case 'true':
                $enteteNumeroSemaine = true;
                break;
            case 'false':
                $enteteNumeroSemaine = false;
                break;
        }

        switch($request->get('entetesaison')){
            case 'true':
                $enteteSaison = true;
                break;
            case 'false':
                $enteteSaison = false;
                break;
        }

        switch($request->get('entetetypehoraire')){
            case 'true':
                $enteteTypeHoraire = true;
                break;
            case 'false':
                $enteteTypeHoraire = false;
                break;
        }

        switch($request->get('entetedateimpression')){
            case 'true':
                $enteteDateImpression = true;
                break;
            case 'false':
                $enteteDateImpression = false;
                break;
        }

        switch($request->get('entetenumerodepage')){
            case 'true':
                $enteteNumeroDePage = true;
                break;
            case 'false':
                $enteteNumeroDePage = false;
                break;
        }

        switch($request->get('impressionEnNoirEtBlanc')){
            case 'true':
                $impressionEnNoirEtBlanc = true;
                break;
            case 'false':
                $impressionEnNoirEtBlanc = false;
                break;
        }

        $margegauche = intval($request->get('margegauche'));
        $margehaute = intval($request->get('margehaute'));
        $margedroite = intval($request->get('margedroite'));

        switch($request->get('autoResizeFonts')){
            case 'true':
                $autoResizeFonts = true;
                break;
            case 'false':
                $autoResizeFonts = false;
                break;
        }
        $horaireResize = $request->get('autoResizeTranchesHoraires');
        switch($horaireResize){
            case 'true':
                $autoResizeTranchesHoraires = true;
                break;
            case 'false':
                $autoResizeTranchesHoraires = false;
                break;
        }
        $horairejoursuivant = $request->get('affichagetranchehorairejoursuivant');
        switch($horairejoursuivant){
            case 'true':
                $affichagetranchehorairejoursuivant = true;
                break;
            case 'false':
                $affichagetranchehorairejoursuivant = false;
                break;
        }


        //les paramêtres du _construct($em,$container,$templateId,$debutPeriodeImpression,$finPeriodeImpression,$aJoursDeLaSemaine,$aIdAvionsAImprimer,$optionImpressionAvions,$impressionEnNoirEtBlanc,$autoResizeFonts,$autoResizeTranchesHoraires,$margeGauche,$margeHaute,$margeDroite)
        $this->monPdf = new PlanningVolPDFPrinter($this->getDoctrine()->getManager(),$this->container,$request->getSession()->get('template'),$dateDebut,$dateDeFin,$aJoursDeLaSemaine,$aIdAvionsAImprimer,$optionImpressionAvions,$impressionEnNoirEtBlanc,$autoResizeFonts,$autoResizeTranchesHoraires,$reperesdurees,$overflowTexteVolsCourts,$dureemaxivolscourts,$enteteCompagnie,$enteteJourSemaine,$enteteNumeroSemaine,$enteteSaison,$enteteTypeHoraire,$enteteDateImpression,$enteteNumeroDePage,$margegauche,$margehaute,$margedroite,$affichagetranchehorairejoursuivant);
        //on retourne le pdf généré
        $em = $this->getDoctrine()->getManager();

        $repoTemplate = $em->getRepository('AirCorsicaXKPlanBundle:Template');
        $templateCurrent = $repoTemplate->find($this->get('session')->get('template'));
        return $this->monPdf->createPDF($templateCurrent);

    }

    /**
     * Fait une maj des attributs des variables de session des parametres du planning de vol
     *
     * @param Request $request
     *
     */
    public function setParametresPlanningVolAction(Request $request)
    {
        //récupération des variables de sessions utilisés dans le panning de vol
        $session = $request->getSession();
        if($request->get('interactionsourisplanning') == "false"){
            $interactionSourisPlanning = false;
        }else{
            $interactionSourisPlanning = true;
        }
        $typedeplanning = $request->get('typedeplanning');
        $typedemodificationplanning = $request->get('typedemodificationplanning');
        $aselectionVolsPlanning = $request->get('aselectionvolsplanning');
        $datePlanningEnCourEdition = $request->get('dateplanningencouredition');
        $zoominterface = intval($request->get('zoominterface'));
        $periodecustom = array('debut'=>$request->get('periodecustomdebut'),'fin'=>$request->get('periodecustomfin'));
        $selectedIdSaison = $request->get('selectedIdSaison');

        $session->set('pv_interactionsouris',$interactionSourisPlanning);
        $session->set('pv_typedeplanning',$typedeplanning);
        $session->set('pv_typedemodification',$typedemodificationplanning);
        $session->set('pv_aselectionvols',$aselectionVolsPlanning);
        $session->set('pv_datepencouredition',$datePlanningEnCourEdition);
        $session->set('pv_zoominterface',$zoominterface);
        $session->set('pv_periodecustom',$periodecustom);
        $session->set('pv_idsaisonselected',$selectedIdSaison);

        return new Response(
            json_encode("[]")
        );
    }

    /**
     * Fait une maj de l'attribut "template" de la variable de session si on le change par l'interface du planning de vol
     *
     * @param string nouveautemplateselectionneid
     */
    public function setTemplateChangeSessionVarAction(Request $request)
    {
        //récupération des variables de sessions utilisés dans le panning de vol
        $session = $request->getSession();
        $nouveauTemplateCourantId = intval($request->get('nouveautemplateselectionneid'));
        $session->set('template',$nouveauTemplateCourantId);

        return new Response(
            json_encode("[]")
        );
    }

    /**
     * Return a json array.
     *
     * @return $avionsdelajournee Un json au format ressource FullCalendar
     */
    public function getAvionsAsJsonAction()
    {

        $em = $this->getDoctrine()->getManager();

        $avionsRessource = '[';

        //on cherche dabords les avions AIRCORSICA
        $aAvion = $em->getRepository('AirCorsicaXKPlanBundle:Avion')->findAllTrieParAffreteOrdreNom();

        foreach ($aAvion as $unAvion){
            $avionsRessource = $avionsRessource. '{ "id": "'.$unAvion->getId().'","title": "'.$unAvion->getNom().'","type":"'.$unAvion->getTypeAvion()->getCodeIATA().'","compagnie":"'.$unAvion->getCompagnie()->getCodeIATA().'"},';
        }

        $avionsRessource = substr($avionsRessource,0,-1); //on enléve la derniére ,
        $avionsRessource = $avionsRessource. ']';

        return new Response(
            json_encode($avionsRessource)
        );
    }

    /**
     * Return a json array.
     *
     * @param timestamp $startTimestamp
     * @param timestamp $endTimestamp
     *
     * @return $volsdelajournee Un json au format event FullCalendar
     */
    public function getTodayVolsAsJsonAction(Request $request)
    {
        //variable récupéré a utiliser pour la requête dans la BDD
        $startTimestamp = intval($request->get('start'));
        $jourCalendrier = date_create();
        $jourCalendrier->setTimestamp($startTimestamp);
        $jourCalendrier->setTime(0,0,0);
        $jourCalendrierPlusUn = clone $jourCalendrier;
        $jourCalendrierPlusUn->modify('+1 day');
        $jourCalendrierMoinsUn = clone $jourCalendrier;
        $jourCalendrierMoinsUn->modify('-1 day');

        $em = $this->getDoctrine()->getManager();


        // LES VOLS
        // --------

        // on récupére les vols en fonctions des critéres suivants:
        // -> de AIR CORSICA
        // -> du template ou l'on travail
        // -> compris entre $jourCalendrier -1 et $jourCalendrier +1
        // -> dernier vol de l'historique (pas les version precedentes)


        $compagnie = $em->getRepository('AirCorsicaXKPlanBundle:Compagnie')->find('1'); //AIR CORSICA à pour id 3 dans la BDB XKPlan
        $criteria = array('compagnie'=>$compagnie);

        $volsEvent = '[';

        $repoTemplate = $em->getRepository('AirCorsicaXKPlanBundle:Template');
        $templateCurrent = $repoTemplate->find($request->getSession()->get('template'));
        $aVols=$em->getRepository('AirCorsicaXKPlanBundle:Vol')->getVolsForDate($templateCurrent,$jourCalendrier);

        //on verifier qu'il n'y ai pas un vol de la veille qui arrive ce jour
        $aVolsDepartJoursPrecedentAtterissageCeJour = $em->getRepository('AirCorsicaXKPlanBundle:Vol')->getDayBeforeVolsArrivingOnDate($templateCurrent,$jourCalendrier);
        $aVerificationChevauchementMemeVolMatinEtSoir = array();
        //si oui on le rajoute au tableau de vols
        if(count($aVolsDepartJoursPrecedentAtterissageCeJour)!=0){
            foreach($aVolsDepartJoursPrecedentAtterissageCeJour as $unVolPartantLaVeille){
                array_push($aVols,$unVolPartantLaVeille);
            }
        }

        $volsEvent = '[';
        foreach ($aVols as $unVol){
            $dateDebutPeriodeVol = $unVol->getPeriodeDeVol()->getDateDebut();
            $dateFinPeriodeVol = $unVol->getPeriodeDeVol()->getDateFin();

            $ajourDeValidite = array_diff($unVol->getPeriodeDeVol()->getJoursDeValidite(), array('-')); //on enléve les - de l'array, il ne reste que les id des jours valide
//            $aPeriodeImmobilisationDeCetAvion = $unVol->getAvion()->getPeriodesImmobilsation()->getValues(); //Recherche de vol d'essai pendant l'immobilisation d'un avion
            $aPeriodeImmobilisationDeCetAvion = $em->getRepository('AirCorsicaXKPlanBundle:PeriodeImmobilisation')->getImmoForAvionAndTemplate($unVol->getAvion(),$em->find('AirCorsicaXKPlanBundle:Template',$this->get('session')->get('template')));


            if( (in_array(strval(date('N', $jourCalendrier->getTimestamp())),$ajourDeValidite)) || (intVal($unVol->getPeriodeDeVol()->getDecollage()->format('Hi'))>intVal($unVol->getPeriodeDeVol()->getAtterissage()->format('Hi'))) ){//si le vol est effectué le jour de la semaine du jour demandé ou bien si l'heure de départ plus grande que heure d'arrivé (vol partant la veille)


                if( ( ($unVol->getPeriodeDeVol()->getDecollage()->getTimestamp()>$unVol->getPeriodeDeVol()->getAtterissage()->getTimestamp()) /*&& (in_array(strval(date('N', $jourCalendrierPlusUn->getTimestamp())),$ajourDeValidite)) && ($jourCalendrierPlusUn->getTimestamp()<=$dateFinPeriodeVol->getTimestamp())*/ ) || ($unVol->getPeriodeDeVol()->getDecollage()->getTimestamp()<$unVol->getPeriodeDeVol()->getAtterissage()->getTimestamp()) ){

                    //si le timestamp de l'heure de décolage est supérieur au timestamp de l'heure d'attérissage (le vol arrive le lendemain) ET SI le jour suivant fait partie des jour ou il y a le vol (genre le mercredi jour suivant du mardi) ET SI le jour suivant est bien inférieur ou égale à la fin de péiode de ce vol
                    //ou bien si le timestamp de l'heure de décolage est inférieur au timestamp de l'heure d'attérissage (cas générale)

                    // on rajoute ce vol au json
                    $volsEvent = $volsEvent. '{';
                    $volsEvent = $volsEvent. '"id": "'.$unVol->getId().'",';
                    $volsEvent = $volsEvent. '"resourceId": "'.$unVol->getAvion()->getId().'",';
                        $heureDepart = $unVol->getPeriodeDeVol()->getDecollage()->format('H:i:s');
                        $heureArrivee = $unVol->getPeriodeDeVol()->getAtterissage()->format('H:i:s');

                    if(intVal($unVol->getPeriodeDeVol()->getDecollage()->format('Hi'))>intVal($unVol->getPeriodeDeVol()->getAtterissage()->format('Hi'))){

                        if( intval(date_diff($unVol->getPeriodeDeVol()->getDateDebut(), $jourCalendrier)->format('%R%a')) == 0 ){
                            //jour de depart du vol
                            $volsEvent = $volsEvent. '"chevauchejour": "soir",';
                            $volsEvent = $volsEvent. '"start": "'.$jourCalendrier->format('Y-m-d').' '.$heureDepart.'",'; //du genre 2017-01-19 09:25:00
                            $volsEvent = $volsEvent. '"end": "'.$jourCalendrierPlusUn->format('Y-m-d').' '.$heureArrivee.'",';
                        }else{
                            if(isset($aVerificationChevauchementMemeVolMatinEtSoir[$unVol->getId()])){//est-ce que ce vol n'existerait pas déja (cas du même vol qui chevauche le matin et le soir)
                                //jour de depart du vol
                                $volsEvent = $volsEvent. '"chevauchejour": "soir",';
                                $volsEvent = $volsEvent. '"start": "'.$jourCalendrier->format('Y-m-d').' '.$heureDepart.'",'; //du genre 2017-01-19 09:25:00
                                $volsEvent = $volsEvent. '"end": "'.$jourCalendrierPlusUn->format('Y-m-d').' '.$heureArrivee.'",';
                            }else {
                                //jour d'arrivée du vol
                                $volsEvent = $volsEvent . '"chevauchejour": "matin",';
                                $volsEvent = $volsEvent . '"start": "' . $jourCalendrierMoinsUn->format('Y-m-d') . ' ' . $heureDepart . '",'; //du genre 2017-01-19 09:25:00
                                $volsEvent = $volsEvent . '"end": "' . $jourCalendrier->format('Y-m-d') . ' ' . $heureArrivee . '",';
                                //cet id vol a ete rajouter de la veille
                                $aVerificationChevauchementMemeVolMatinEtSoir[$unVol->getId()] = $unVol->getNumero();
                            }
                        }
                     }else{

                            $volsEvent = $volsEvent . '"chevauchejour": "non",';
                            $volsEvent = $volsEvent . '"start": "' . $jourCalendrier->format('Y-m-d') . ' ' . $heureDepart . '",'; //du genre 2017-01-19 09:25:00
                            $volsEvent = $volsEvent . '"end": "' . $jourCalendrier->format('Y-m-d') . ' ' . $heureArrivee . '",';

                     }
                    $volsEvent = $volsEvent. '"villeDepart": "'.$unVol->getLigne()->getAeroportDepart()->getCodeIATA().'",';
                    $volsEvent = $volsEvent. '"villeArrivee": "'.$unVol->getLigne()->getAeroportArrivee()->getCodeIATA().'",';
                    $volsEvent = $volsEvent. '"codeVol": "'.$unVol->getNumero().'",';
                    $volsEvent = $volsEvent. '"typeVol": "'.$unVol->getTypeDeVol()->getCodeType().'",';
                    $volsEvent = $volsEvent. '"title": "champs fc obligatoire",';
                    $volsEvent = $volsEvent. '"textColor": "#000000",';
                    $volsEvent = $volsEvent. '"backgroundColor": "'.$unVol->getTypeDeVol()->getCodeCouleur().'",';
                    $volsEvent = $volsEvent. '"borderColor": "'.$unVol->getTypeDeVol()->getCodeCouleur().'",';
                    if( $unVol->getPeriodeDeVol()->getEtat() == "send"){
                        $volsEvent = $volsEvent. '"msgEnvoye": "true",';
                    }else{
                        $volsEvent = $volsEvent. '"msgEnvoye": "false",';
                    }
                    $volsEvent = $volsEvent. '"durationEditable": "false",';
                    $dansUnePeriodeImmo = false;
                    foreach ($aPeriodeImmobilisationDeCetAvion as $unePeriodeDImmobilisation) {
                        if ( ( intval(date_diff($unePeriodeDImmobilisation->getDateDebut(), $jourCalendrier)->format('%R%a')) >= 0 ) && ( intval(date_diff($unePeriodeDImmobilisation->getDateFin(), $jourCalendrier)->format('%R%a')) <= 0 ) ){
                            $dansUnePeriodeImmo = true;
                            break;
                        }
                    }
                    if($dansUnePeriodeImmo == true){
                        $volsEvent = $volsEvent . '"avecUnAvionImmobilise": "true"';
                    }else{
                        $volsEvent = $volsEvent . '"avecUnAvionImmobilise": "false"';
                    }
                    $volsEvent = $volsEvent. '},';

                }
            }


        }


        // LES AVIONS IMMOBILISES
        // ----------------------
        $aAvion = $em->getRepository('AirCorsicaXKPlanBundle:Avion')
                    ->getAvionsImmobilisesForDate($jourCalendrier,$em->find('AirCorsicaXKPlanBundle:Template',$this->get('session')->get('template')));

        $compteurImmo=0;
        foreach ($aAvion as $unAvion){

            $aPeriodeImmobilisationDeCetAvion = $em->getRepository('AirCorsicaXKPlanBundle:PeriodeImmobilisation')
                    ->getImmoForAvionAndTemplate($unAvion,$em->find('AirCorsicaXKPlanBundle:Template',$this->get('session')->get('template')));

                    $compteurImmo++;

                     $volsEvent = $volsEvent. '{';
                     $volsEvent = $volsEvent. '"id": "Immo_'.$compteurImmo.'",';
                     $volsEvent = $volsEvent. '"resourceId": "'.$unAvion->getId().'",';
                     $volsEvent = $volsEvent. '"start": "'.$jourCalendrier->format('Y-m-d').' 00:00:00",';
                     $volsEvent = $volsEvent. '"end": "'.$jourCalendrier->format('Y-m-d').' 23:59:59",';
                     $volsEvent = $volsEvent. '"immobilisation": "true",';
                     $volsEvent = $volsEvent. '"title": "champs fc obligatoire",';
                     $volsEvent = $volsEvent. '"causeimmobilisation": "Immobilisé",';
                     $volsEvent = $volsEvent. '"periodeimmobilisation": "';
                     foreach ($aPeriodeImmobilisationDeCetAvion as $unePeriodeDImmobilisation) {
                         if($unePeriodeDImmobilisation->getDateFin()->format('Y')>=$jourCalendrier->format('Y')) {
                             $volsEvent = $volsEvent . '\ndu ' . $unePeriodeDImmobilisation->getDateDebut()->format('d-m-Y') . ' au ' . $unePeriodeDImmobilisation->getDateFin()->format('d-m-Y') . ',';
                         }
                     }
                     $volsEvent = substr($volsEvent,0,-1).'",';
                     $volsEvent = $volsEvent. '"textColor": "#FFFFFF",';
                     $volsEvent = $volsEvent. '"borderColor": "#AA0000",';
                     $volsEvent = $volsEvent. '"className": "fc-timeline-immobilisation"';
                     $volsEvent = $volsEvent. '},';

        }

        if( $volsEvent != '[' ) {
            $volsEvent = substr($volsEvent, 0, -1); //on enléve la derniére ,
            $volsEvent = $volsEvent . ']';
        }else{
            $volsEvent='[]';
        }

        return new Response(
            json_encode($volsEvent)
        );
    }

    /**
     * Retourne la durée de vol pour le nouvel avion (0 si elle n'existe pas cad non définiie dans la BDD)
     *
     * @param Vol $volOrigine
     * @param Avion $nouveauAvion
     * @param \DateTime $debutPeriodeATester
     * @param \DateTime $finPeriodeATester
     * @param EntityManager $em
     *
     */
    private function getPlaneVolDurationForAVolForAPeriod($volOrigine,$nouveauAvion,$debutPeriodeATester,$finPeriodeATester,$em){

            //recherche des aeroports

            //FIX BUG trouvé dans enregistrement dans la BDD codeIATA qui n'est pas unique pour 2 aeroport différent: HYERES LE PALYVESTRE et TOULON ont tout les deux TLN comme code IATA
            //$criteria = array('codeIATA'=>$volOrigine->getLigne()->getAeroportDepart()->getCodeIATA());
            $criteria = array('id'=>$volOrigine->getLigne()->getAeroportDepart()->getId());
            $aeroportdepart = $em->getRepository('AirCorsicaXKPlanBundle:Aeroport')->findBy($criteria);
            //FIX BUG trouvé dans enregistrement dans la BDD codeIATA qui n'est pas unique pour 2 aeroport différent: HYERES LE PALYVESTRE et TOULON ont tout les deux TLN comme code IATA
            //$criteria = array('codeIATA'=>$volOrigine->getLigne()->getAeroportArrivee()->getCodeIATA());
            $criteria = array('id'=>$volOrigine->getLigne()->getAeroportArrivee()->getId());
            $aeroportarrivee = $em->getRepository('AirCorsicaXKPlanBundle:Aeroport')->findBy($criteria);

            //recherche de la ligne correstpondant
            $criteria = array('aeroportDepart'=>$aeroportdepart[0],'aeroportArrivee'=>$aeroportarrivee[0]);
            $ligne = $em->getRepository('AirCorsicaXKPlanBundle:Ligne')->findBy($criteria);

            //recherche des temps de vol de ce typeAvion
            $criteria = array('ligne'=>$ligne[0],'typeAvion'=>$nouveauAvion->getTypeAvion());
            $aTempsDeVol = $em->getRepository('AirCorsicaXKPlanBundle:TempsDeVol')->findBy($criteria);

            //Si aucun temps de vol n'a été trouvé on fait une recherche de la ligne inverse !les temps de vol sont identique)
            if(count($aTempsDeVol) == 0){
                //recherche de la ligne inverse
                $criteria = array('aeroportDepart'=>$aeroportarrivee[0],'aeroportArrivee'=>$aeroportdepart[0]);
                $ligne = $em->getRepository('AirCorsicaXKPlanBundle:Ligne')->findBy($criteria);
                //recherche des temps de vol de ce typeAvion
                $criteria = array('ligne'=>$ligne[0],'typeAvion'=>$nouveauAvion->getTypeAvion());
                $aTempsDeVol = $em->getRepository('AirCorsicaXKPlanBundle:TempsDeVol')->findBy($criteria);
            }

            //recherche des saisons qui inclue la période de modification
            $nouvelledureeduvol = 0;
            $aperiodesSaisons = $em->getRepository('AirCorsicaXKPlanBundle:PeriodeSaison')->findAll();
            foreach ($aperiodesSaisons as $unePeriodeSaison) {
                if( (intval(date_diff($unePeriodeSaison->getDateDebut(),$debutPeriodeATester)->format('%R%a')) >=0 ) && (intval(date_diff($finPeriodeATester,$unePeriodeSaison->getDateFin())->format('%R%a')) >=0)  ) {//cette période est compatible
                    //on compare les temps de vols de la ligne et du nouveau type avion
                    foreach ($aTempsDeVol as $unTempDeVol) {
                        //si cette saison est définie dans le temps de vol
                        if($unTempDeVol->getSaison()->getId() == $unePeriodeSaison->getId()) {
                            $nouvelledureeduvol = $unTempDeVol->getDuree();
                        }
                    }

                }
            }

        return $nouvelledureeduvol;

    }


    /**
     * Est-ce que le temps de vol va changé suite au changement d'avion
     *
     * @param Vol $volOrigine
     * @parzm Avion $nouveauAvion
     * @param \DateTime $debutPeriodeATester
     * @param \DateTime $finPeriodeATester
     *
     * Retourne un booléen: true si 'lhorzire a changé et false si il reste identique
     *
     */
    private function didDureeDeVolChangedWithNewPlane(Request $request,$volOrigine,$nouveauAvion,$debutPeriodeATester,$finPeriodeATester,$em){

        $durationChange = false;

        //on cherche la nouvelle durée de vol avec le nouveau avion
        $nouveldureeduvol = $this->getPlaneVolDurationForAVolForAPeriod($volOrigine,$nouveauAvion,$debutPeriodeATester,$finPeriodeATester,$em);
        if( intval($nouveldureeduvol) == 0 ){//le temps de vol n'est pas d&finis pour le nouvel avion sur la période de changement
            $nouveldureeduvol = 59;
        }

        //on la compare avec la durée de vol
        if( intval($volOrigine->getTempsDeVol('total_minutes')) != intval($nouveldureeduvol) ){
            $durationChange = true;
        }

        return $durationChange;

    }

    /**
     * Récupération de la période résultant de la période du vol et de l'interval de recherche (période ou saison)
     *
     * @param Vol $vol
     * @param \DateTime $periodeousaisonjourdebut
     * @param \DateTime $periodeousaisonjourfin
     *
     * Retourne un array de 2 dateTime "intersectionDebut" et "intersectionFin" qui sera la période de modification
     *
     */
    private function getIntersectionPeriod(Request $request,$vol,$periodeousaisonjourdebut,$periodeousaisonjourfin,$em){

        //on récupére les période définies pour le vol à traiter
        $periodeDebut_vol = $vol->getPeriodeDeVol()->getDateDebut();
        $periodeFin_vol = $vol->getPeriodeDeVol()->getDateFin();

        //soustraction des périodes de modification invalide
        if( intval(date_diff($periodeDebut_vol, $periodeousaisonjourdebut)->format('%R%a')) >0 ){
            $periodeousaisonjourdebut = $periodeDebut_vol;
        }
        if( intval(date_diff($periodeFin_vol,$periodeousaisonjourfin)->format('%R%a')) >0 ){
            $periodeousaisonjourfin = $periodeFin_vol;
        }

        return ['dateDebut'=>$periodeousaisonjourdebut,'dateFin'=>$periodeousaisonjourfin];

    }


    /**
     * Est-ce que les 2 array de jours de validités sont identique
     *
     * @param Array $ajoursdeValidites_A
     * @param Array $ajoursdeValidites_B
     *
     * Retourne true ou false
     *
     */
    private function didJoursDeValiditesTheSame($ajoursdeValidites_A,$ajoursdeValidites_B){

        $result = true;

        if ( count($ajoursdeValidites_A) == count($ajoursdeValidites_B) ){

            for($i =0;$i<count($ajoursdeValidites_A);$i++) {

                if($ajoursdeValidites_A[$i] != $ajoursdeValidites_B[$i]){
                    $result = false;
                }

            }

        }else{
            $result = false;
        }

        return $result;

    }


    /**
     * Récupération des actions à effectuer pour un vol sur la période de modification
     * De plus cette période sera optimisé en fonction des jours de validité d'application à partir de la période de modification
     *
     * @param Vol $vol
     * @param Array $ajoursdelasemaineamodifer
     * @param \DateTime $periodedemodificationjourdebut
     * @param \DateTime $periodedemodificationjourfin
     *
     * Retourne un array de 2 dateTime "modificationDebut" et "modificationFin"
     *
     */

    private function getOptimisedActions(Request $request, Vol $vol, $newAvion, \DateTime $intersectionPeriodeDebut,\DateTime $intersectionPeriodeFin,$ajoursdelasemaineamodifer,$em){

        $aoptimisation = array();
        $days = array('0'=>'monday','1' => 'tuesday','2' => 'wednesday','3'=>'thursday','4' =>'friday','5' => 'saturday','6'=>'sunday');
        $datedebutestundesjourdearray = false;
        $datefinestundesjourdearray = false;

//        // Est-ce que les avions sont du même type
//        $typeavionidentique = false;
//        if($vol->getAvion()->getTypeAvion() == $newAvion->getTypeAvion()){
//            $typeavionidentique = true;
//        }
//
//        // Est-ce que la compagnie est identique
//        $compagnieidentique = false;
//        if($vol->getAvion()->getCompagnie()->getCodeIATA() == $newAvion->getCompagnie()->getCodeIATA()){
//            $compagnieidentique = true;
//        }
//
//        // Est-ce que le temps de vol est identique
//        $isThisVolHaveNewAtterissageTime = false;
//        if($this->didDureeDeVolChangedWithNewPlane($request,$vol,$newAvion,$intersectionPeriodeDebut,$intersectionPeriodeFin,$em) == true){
//            $isThisVolHaveNewAtterissageTime = true;
//        }

        // Est-ce que les jours de validités sont identiques ?
        $lesjoursdevaliditessontidentique = false;
        if( $this->didJoursDeValiditesTheSame($vol->getPeriodeDeVol()->getJoursDeValidite(),$ajoursdelasemaineamodifer) ){
            $lesjoursdevaliditessontidentique = true;
        }

        // ----------------------------------------------------------------------------------------------------------
        // Calcul des dates de la période de modification optimisé en  fonction des jours de la semaine selectionnés
        // ----------------------------------------------------------------------------------------------------------

        //Date de début optimisé
        //----------------------

        //Est-ce que la date de début est un des jours de $arrayjoursdelasemaineamodifer

        //pour chaque jour de validités (lundi, mercredi ...)
        for($i =0;$i<count($ajoursdelasemaineamodifer);$i++) {

            // si jour début est le même jour de la semaine que le jour de validité de la boucle
            if ((intval($ajoursdelasemaineamodifer[$i]) + 1) == $intersectionPeriodeDebut->format('N')) {
                $datedebutestundesjourdearray = true;
                //on a trouvé la date de début optimisé
                $optimisePeriodeDebut = $intersectionPeriodeDebut;
            }

        }

        //Sinon quel est la plus petite date à partir de date début qui est un des jours de $arrayjoursdelasemaineamodifer
        if($datedebutestundesjourdearray == false){

            $ajoursatester = array();
            $atimestampjoursatester = array();

            //pour chaque jour de validités (lundi, mercredi ...) on recherche son jour suivant
            for($i =0;$i<count($ajoursdelasemaineamodifer);$i++) {
                $ajoursatester[$i] = clone $intersectionPeriodeDebut;
                $ajoursatester[$i]->modify('next ' . $days[$ajoursdelasemaineamodifer[$i]]);
                $atimestampjoursatester[$i] = $ajoursatester[$i]->getTimestamp();
            }

            //on recherche dans le tableau de jours suivnat la date la plus petite
            //on cherche l'indice i qui correspond a la plus petite date
            $pluspetitedateauformattimestamp = min($atimestampjoursatester);
            $indicequelonrecherche = 0;
            for($i=0;$i<=count($atimestampjoursatester)-1;$i++){
                if($atimestampjoursatester[$i] == $pluspetitedateauformattimestamp){
                    $indicequelonrecherche = $i;
                    break;
                }
            }
            //on a trouvé la date de début optimisé
            $optimisePeriodeDebut = $ajoursatester[$indicequelonrecherche];

        }

        //Date de fin optimisé
        //----------------------

        //Est-ce que la date de début est un des jours de $arrayjoursdelasemaineamodifer

        //pour chaque jour de validités (lundi, mercredi ...)
        for($i =0;$i<count($ajoursdelasemaineamodifer);$i++) {

            // si jour début est le même jour de la semaine que le jour de validité de la boucle
            if ((intval($ajoursdelasemaineamodifer[$i]) + 1) == $intersectionPeriodeFin->format('N')) {
                $datefinestundesjourdearray = true;
                //on a trouvé la date de début optimisé
                $optimisePeriodeFin = $intersectionPeriodeFin;
            }

        }

        //Sinon quel est la plus grande date avant la date fin qui est un des jours de $arrayjoursdelasemaineamodifer
        if($datefinestundesjourdearray == false) {

            $ajoursatester = array();
            $atimestampjoursatester = array();

            //pour chaque jour de validités (lundi, mercredi ...) on recherche son jour précédent
            for($i =0;$i<count($ajoursdelasemaineamodifer);$i++) {
                $ajoursatester[$i] = clone $intersectionPeriodeFin;
                $ajoursatester[$i]->modify('last ' . $days[$ajoursdelasemaineamodifer[$i]]);
                $atimestampjoursatester[$i] = $ajoursatester[$i]->getTimestamp();
            }

            //on recherche dans le tableau de jours suivnat la date la plus petite
            //on cherche l'indice i qui correspond a la plus petite date
            $plusgrandedateauformattimestamp = max($atimestampjoursatester);
            $indicequelonrecherche = 0;
            for($i=0;$i<=count($atimestampjoursatester)-1;$i++){
                if($atimestampjoursatester[$i] == $plusgrandedateauformattimestamp){
                    $indicequelonrecherche = $i;
                    break;
                }
            }

            //on a trouvé la date de fin optimisé
            $optimisePeriodeFin = $ajoursatester[$indicequelonrecherche];
        }


        // -----------------------------
        //Calcul des jours de validités
        // ------------------------------

        //Jours de validités d'origine
        $ajoursDeValiditesOrigine = $vol->getPeriodeDeVol()->getJoursDeValidite();

        //Jours de validités sauf jours du changement d'avion
        $ajoursDeValiditesSaufJoursChangementAvion = $ajoursDeValiditesOrigine;
        for($i =0;$i<count($ajoursdelasemaineamodifer);$i++) {
            $ajoursDeValiditesSaufJoursChangementAvion[$ajoursdelasemaineamodifer[$i]]="-";
        }

        //Uniquement Jours de validités du changement d'avion
        $ajoursDeValiditesUniquementJoursChangementAvion = ["0"=>"-","1"=>"-","2"=>"-","3"=>"-","4"=>"-","5"=>"-","6"=>"-"];
        for($i =0;$i<count($ajoursdelasemaineamodifer);$i++) {
            $ajoursDeValiditesUniquementJoursChangementAvion[$ajoursdelasemaineamodifer[$i]]=intval($ajoursdelasemaineamodifer[$i]) +1;
        }


        // -----------------------------------------------
        //Calcul des intervals du vol d'origine à modifier
        // -----------------------------------------------
        if(
            (intval(date_diff($vol->getPeriodeDeVol()->getDateDebut(), $optimisePeriodeDebut)->format('%R%a')) ==0 ) &&
            (intval(date_diff($vol->getPeriodeDeVol()->getDateFin(), $optimisePeriodeFin)->format('%R%a')) ==0 )
        ){
            //-----------------------------------------------------------------------------------
            // Cas1 : full
            //      A -------------- B (vol avec avion origine)
            //      C -------------- D (nouveau avion)
            //Le debut de la période du vol est le jour de début de la premiére période transmise
            // et la fin de la période du vol est le jour de fin de la derniére période transmise
            //-----------------------------------------------------------------------------------

            $aoptimisation['type']='full';

            // Période A --- B
            if($lesjoursdevaliditessontidentique == true) { //le changement d'avion se fait sur tous les jours de validités

                $aoptimisation['voloriginemodified'][0]['action'] = 'modifier';
                $aoptimisation['voloriginemodified'][0]['isThisVolHaveNewPlane'] = true;
                //$aoptimisation['voloriginemodified'][0]['isThisVolHaveNewAtterissageTime'] = $isThisVolHaveNewAtterissageTime;
                $aoptimisation['voloriginemodified'][0]['dateDebut'] = $vol->getPeriodeDeVol()->getDateDebut();
                $aoptimisation['voloriginemodified'][0]['dateFin'] = $vol->getPeriodeDeVol()->getDateFin();
                $aoptimisation['voloriginemodified'][0]['joursDeValidites'] = $ajoursDeValiditesOrigine;

            }else{ //le changement d'avion se fait sur une partie des jours de validités

                $aoptimisation['voloriginemodified'][0]['action'] = 'modifier'; //laisser les jours de validité ou il n'y a pas de changement d'avion
                $aoptimisation['voloriginemodified'][0]['isThisVolHaveNewPlane'] = false;
                //$aoptimisation['voloriginemodified'][0]['isThisVolHaveNewAtterissageTime'] = false;
                $aoptimisation['voloriginemodified'][0]['dateDebut'] = $vol->getPeriodeDeVol()->getDateDebut();
                $aoptimisation['voloriginemodified'][0]['dateFin'] = $vol->getPeriodeDeVol()->getDateFin();
                $aoptimisation['voloriginemodified'][0]['joursDeValidites'] = $ajoursDeValiditesSaufJoursChangementAvion;

                $aoptimisation['volnew'][0]['action'] = 'creer'; //créer les jours de validité avec le nouvel d'avion
                $aoptimisation['volnew'][0]['isThisVolHaveNewPlane'] = true;
                //$aoptimisation['volnew'][0]['isThisVolHaveNewAtterissageTime'] = $isThisVolHaveNewAtterissageTime;
                $aoptimisation['volnew'][0]['dateDebut'] = $vol->getPeriodeDeVol()->getDateDebut();
                $aoptimisation['volnew'][0]['dateFin'] = $vol->getPeriodeDeVol()->getDateFin();
                $aoptimisation['volnew'][0]['joursDeValidites'] = $ajoursDeValiditesUniquementJoursChangementAvion;
            }

        }else if(
            (intval(date_diff($vol->getPeriodeDeVol()->getDateDebut(), $optimisePeriodeDebut)->format('%R%a')) ==0 ) &&
            (intval(date_diff($optimisePeriodeFin,$vol->getPeriodeDeVol()->getDateFin())->format('%R%a')) >0 )
        ) {
            //-----------------------------------------------------------------------------------
            // Cas2: left
            //      A -------------- B (vol)
            //      C ------ D         (modif)
            //Le debut de la période du vol est le jour de début de la premiére période transmise
            //-----------------------------------------------------------------------------------

            $aoptimisation['type']='left';

            // Période A --- D
            if($lesjoursdevaliditessontidentique == true) { //le changement d'avion se fait sur tous les jours de validités

                $indicesuivant = 0;

                $aoptimisation['voloriginemodified'][0]['action'] = 'modifier';
                $aoptimisation['voloriginemodified'][0]['isThisVolHaveNewPlane'] = true;
                //$aoptimisation['voloriginemodified'][0]['isThisVolHaveNewAtterissageTime'] = $isThisVolHaveNewAtterissageTime;
                $aoptimisation['voloriginemodified'][0]['dateDebut'] = $vol->getPeriodeDeVol()->getDateDebut();
                $aoptimisation['voloriginemodified'][0]['dateFin'] = $optimisePeriodeFin;
                $aoptimisation['voloriginemodified'][0]['joursDeValidites'] = $ajoursDeValiditesOrigine;

            }else{ //le changement d'avion se fait sur une partie des jours de validités

                $indicesuivant = 1;

                $aoptimisation['voloriginemodified'][0]['action'] = 'modifier'; //laisser les jours de validité ou il n'y a pas de changement d'avion
                $aoptimisation['voloriginemodified'][0]['isThisVolHaveNewPlane'] = false;
                //$aoptimisation['voloriginemodified'][0]['isThisVolHaveNewAtterissageTime'] = false;
                $aoptimisation['voloriginemodified'][0]['dateDebut'] = $vol->getPeriodeDeVol()->getDateDebut();
                $aoptimisation['voloriginemodified'][0]['dateFin'] = $optimisePeriodeFin;
                $aoptimisation['voloriginemodified'][0]['joursDeValidites'] = $ajoursDeValiditesSaufJoursChangementAvion;

                $aoptimisation['volnew'][0]['action'] = 'creer'; //créer les jours de validité avec le nouvel d'avion
                $aoptimisation['volnew'][0]['isThisVolHaveNewPlane'] = true;
                //$aoptimisation['volnew'][0]['isThisVolHaveNewAtterissageTime'] = $isThisVolHaveNewAtterissageTime;
                $aoptimisation['volnew'][0]['dateDebut'] = $vol->getPeriodeDeVol()->getDateDebut();
                $aoptimisation['volnew'][0]['dateFin'] = $optimisePeriodeFin;
                $aoptimisation['volnew'][0]['joursDeValidites'] = $ajoursDeValiditesUniquementJoursChangementAvion;

            }

            // Période D+1 -- B
            $datetemp = clone $optimisePeriodeFin;
            $datetemp->modify('+1 day');
            $aoptimisation['volnew'][$indicesuivant]['action'] = 'creer';
            $aoptimisation['volnew'][$indicesuivant]['isThisVolHaveNewPlane'] = false;
            //$aoptimisation['volnew'][$indicesuivant]['isThisVolHaveNewAtterissageTime'] = false;
            $aoptimisation['volnew'][$indicesuivant]['dateDebut'] = $datetemp;
            $aoptimisation['volnew'][$indicesuivant]['dateFin'] = $ajoursDeValiditesOrigine;
            $aoptimisation['volnew'][$indicesuivant]['joursDeValidites'] = $vol->getPeriodeDeVol()->getJoursDeValidite();

        }else if(
            (intval(date_diff($vol->getPeriodeDeVol()->getDateFin(), $optimisePeriodeFin)->format('%R%a')) == 0 ) &&
            (intval(date_diff($vol->getPeriodeDeVol()->getDateDebut(), $optimisePeriodeDebut)->format('%R%a')) >0 )
        ) {
            //-------------------------------------------------------------------------------
            // Cas3: right
            //      A -------------- B (vol)
            //              C ------ D (modif)
            //La fin de la période du vol est le jour de fin de la derniére période transmise
            //-------------------------------------------------------------------------------

            $aoptimisation['type']='right';

            // Période C -- B
            if($lesjoursdevaliditessontidentique == true) { //le changement d'avion se fait sur tous les jours de validités

                $indicesuivant = 0;

                $aoptimisation['voloriginemodified'][0]['action'] = 'modifier';
                $aoptimisation['voloriginemodified'][0]['isThisVolHaveNewPlane'] = true;
                //$aoptimisation['voloriginemodified'][0]['isThisVolHaveNewAtterissageTime'] = $isThisVolHaveNewAtterissageTime;
                $aoptimisation['voloriginemodified'][0]['dateDebut'] = $optimisePeriodeDebut;
                $aoptimisation['voloriginemodified'][0]['dateFin'] = $vol->getPeriodeDeVol()->getDateFin();
                $aoptimisation['voloriginemodified'][0]['joursDeValidites'] = $ajoursDeValiditesOrigine;

            }else { //le changement d'avion se fait sur une partie des jours de validités

                $indicesuivant = 1;

                $aoptimisation['voloriginemodified'][0]['action'] = 'modifier'; //laisser les jours de validité ou il n'y a pas de changement d'avion
                $aoptimisation['voloriginemodified'][0]['isThisVolHaveNewPlane'] = false;
                //$aoptimisation['voloriginemodified'][0]['isThisVolHaveNewAtterissageTime'] = false;
                $aoptimisation['voloriginemodified'][0]['dateDebut'] = $optimisePeriodeDebut;
                $aoptimisation['voloriginemodified'][0]['dateFin'] = $vol->getPeriodeDeVol()->getDateFin();
                $aoptimisation['voloriginemodified'][0]['joursDeValidites'] = $ajoursDeValiditesSaufJoursChangementAvion;

                $aoptimisation['volnew'][0]['action'] = 'creer'; //créer les jours de validité avec le nouvel d'avion
                $aoptimisation['volnew'][0]['isThisVolHaveNewPlane'] = true;
                //$aoptimisation['volnew'][0]['isThisVolHaveNewAtterissageTime'] = $isThisVolHaveNewAtterissageTime;
                $aoptimisation['volnew'][0]['dateDebut'] = $optimisePeriodeDebut;
                $aoptimisation['volnew'][0]['dateFin'] = $vol->getPeriodeDeVol()->getDateFin();
                $aoptimisation['volnew'][0]['joursDeValidites'] = $ajoursDeValiditesUniquementJoursChangementAvion;

            }

            // Période A --- C-1
            $datetemp = clone $optimisePeriodeDebut;
            $datetemp->modify('-1 day');
            $aoptimisation['volnew'][$indicesuivant]['action'] = 'modifier';
            $aoptimisation['volnew'][$indicesuivant]['isThisVolHaveNewPlane'] = false;
            //$aoptimisation['volnew'][$indicesuivant]['isThisVolHaveNewAtterissageTime'] = false;
            $aoptimisation['volnew'][$indicesuivant]['dateDebut'] = $vol->getPeriodeDeVol()->getDateDebut();
            $aoptimisation['volnew'][$indicesuivant]['dateFin'] = $datetemp;
            $aoptimisation['volnew'][$indicesuivant]['joursDeValidites'] = $ajoursDeValiditesOrigine;

        }else {
            //----------------------------------------------------------
            // Cas4: middle
            //      A -------------- B (vol)
            //          C ----- D      (modif)
            //Les périodes transmises sont inclus dans la période du vol
            //----------------------------------------------------------

            $aoptimisation['type']='middle';

            // Période C ---- D
            if($lesjoursdevaliditessontidentique == true) { //le changement d'avion se fait sur tous les jours de validités

                $indicesuivant = 0;

                $aoptimisation['voloriginemodified'][0]['action'] = 'modifier';
                $aoptimisation['voloriginemodified'][0]['isThisVolHaveNewPlane'] = true;
                //$aoptimisation['voloriginemodified'][0]['isThisVolHaveNewAtterissageTime'] = $isThisVolHaveNewAtterissageTime;
                $aoptimisation['voloriginemodified'][0]['dateDebut'] = $optimisePeriodeDebut;
                $aoptimisation['voloriginemodified'][0]['dateFin'] = $optimisePeriodeFin;
                $aoptimisation['voloriginemodified'][0]['joursDeValidites'] = $ajoursDeValiditesOrigine;

            }else { //le changement d'avion se fait sur une partie des jours de validités

                $indicesuivant = 1;

                $aoptimisation['voloriginemodified'][0]['action'] = 'modifier'; //laisser les jours de validité ou il n'y a pas de changement d'avion
                $aoptimisation['voloriginemodified'][0]['isThisVolHaveNewPlane'] = false;
                //$aoptimisation['voloriginemodified'][0]['isThisVolHaveNewAtterissageTime'] = false;
                $aoptimisation['voloriginemodified'][0]['dateDebut'] = $optimisePeriodeDebut;
                $aoptimisation['voloriginemodified'][0]['dateFin'] = $optimisePeriodeFin;
                $aoptimisation['voloriginemodified'][0]['joursDeValidites'] = $ajoursDeValiditesSaufJoursChangementAvion;

                $aoptimisation['volnew'][0]['action'] = 'creer'; //créer les jours de validité avec le nouvel d'avion
                $aoptimisation['volnew'][0]['isThisVolHaveNewPlane'] = true;
                //$aoptimisation['volnew'][0]['isThisVolHaveNewAtterissageTime'] = $isThisVolHaveNewAtterissageTime;
                $aoptimisation['volnew'][0]['dateDebut'] = $optimisePeriodeDebut;
                $aoptimisation['volnew'][0]['dateFin'] = $optimisePeriodeFin;
                $aoptimisation['volnew'][0]['joursDeValidites'] = $ajoursDeValiditesUniquementJoursChangementAvion;

            }

            // Période A --- C-1
            $datetemp = clone $optimisePeriodeDebut;
            $datetemp->modify('-1 day');
            $aoptimisation['volnew'][$indicesuivant]['action'] = 'creer'; //infos connues par les GDS
            $aoptimisation['volnew'][$indicesuivant]['isThisVolHaveNewPlane'] = false;
            //$aoptimisation['volnew'][$indicesuivant]['isThisVolHaveNewAtterissageTime'] = false;
            $aoptimisation['volnew'][$indicesuivant]['dateDebut'] = $vol->getPeriodeDeVol()->getDateDebut();
            $aoptimisation['volnew'][$indicesuivant]['dateFin'] = $datetemp;
            $aoptimisation['volnew'][$indicesuivant]['joursDeValidites'] = $ajoursDeValiditesOrigine;

            // Période D+1 --- B
            $datetemp = clone $optimisePeriodeFin;
            $datetemp->modify('+1 day');
            $aoptimisation['volnew'][$indicesuivant+1]['action'] = 'creer'; //infos connues par les GDS
            $aoptimisation['volnew'][$indicesuivant+1]['isThisVolHaveNewPlane'] = false;
            //$aoptimisation['volnew'][$indicesuivant+1]['isThisVolHaveNewAtterissageTime'] = false;
            $aoptimisation['volnew'][$indicesuivant+1]['dateDebut'] = $datetemp;
            $aoptimisation['volnew'][$indicesuivant+1]['dateFin'] = $vol->getPeriodeDeVol()->getDateFin();
            $aoptimisation['volnew'][$indicesuivant+1]['joursDeValidites'] = $ajoursDeValiditesOrigine;

        }

        //on retourne les infos
        return $aoptimisation;

    }

    /**
     * Sauvegarde en attente d'un Vol dans la BDD (persist)
     */
    //private function persistVolNew($volOrigineACloner, $newAvion, $newPeriodeDebut, $newPeriodeFin, $newDecollage, $newAtterissage, $anewJoursDeValidites, $newEtat, $em){
    private function persistVolNew($volOrigineCloneValeursFiges,$onViensDeCreerLeVol,$newAvion,$isThisVolHaveNewPlane,$newPeriodeDebut,$newPeriodeFin,$newheureatterissage,$anewJoursDeValidites,$em){

        //-----------------------------------------------------------------------
        //création du nouveau vol avec le nouvel avion pour une période de 1 jour
        //-----------------------------------------------------------------------

        //on instancie un nouve objet vol
        //-------------------------------
        $volNew = new Vol();

        //id vide car il est généré à la création
        //---------------------------------------
        $volNew->setId('');

        //numero de vol
        //-------------
        $volNew->setNumero($volOrigineCloneValeursFiges->getNumero());

        //le nouvel avion ou pas
        //----------------------
        if($isThisVolHaveNewPlane == true) {
            $volNew->setAvion($newAvion);
        }else{
            $volNew->setAvion($volOrigineCloneValeursFiges->getAvion());
        }

        //la nouvelle compagnie ou pas
        //----------------------------
        if($isThisVolHaveNewPlane == true) {
            $volNew->setCompagnie($newAvion->getCompagnie());
        }else{
            $volNew->setCompagnie($volOrigineCloneValeursFiges->getCompagnie());
        }

        //la ligne
        //--------
        $volNew->setLigne($volOrigineCloneValeursFiges->getLigne());

        //le type de vol
        //--------------
        $volNew->setTypeDeVol($volOrigineCloneValeursFiges->getTypeDeVol());

        //l'affretement
        //-------------
        //$volNew->setAffretement($volOrigineCloneValeursFiges->getAffretement());
        if($isThisVolHaveNewPlane == true) {
            if ($newAvion->getCompagnie()->getCodeIATA() != "XK") {
                $monAffretement = $em->getRepository('AirCorsicaXKPlanBundle:Affretement')->find(3); //1 -> Aucun //2 -> Affrètement technique, 3 -> Affrètement commercial
                $volNew->setAffretement($monAffretement);
            } else {
                $volNew->setAffretement($volOrigineCloneValeursFiges->getAffretement());
            }
        }else{
            $volNew->setAffretement($volOrigineCloneValeursFiges->getAffretement());
        }

        //le template
        //-----------
        $volNew->setTemplate($volOrigineCloneValeursFiges->getTemplate());

        //les codesShare du Vol
        //---------------------
        $volNew->setCodesShareFromList(explode("_",$volOrigineCloneValeursFiges->getCodesShareVolToString()));

        //periode de vol
        //--------------
        $periodevolNew = new PeriodeDeVol();
        $periodevolNew->setDateDebut($newPeriodeDebut);
        $periodevolNew->setDateFin($newPeriodeFin);
        $volNew->setPeriodeDeVol($periodevolNew);

        //jour de validité pour ce jour
        //-----------------------------
        $volNew->getPeriodeDeVol()->setJoursDeValidite($anewJoursDeValidites);

        //decollage et atterissage avec le nouvel avion
        //---------------------------------------------
        $volNew->getPeriodeDeVol()->setDecollage( $volOrigineCloneValeursFiges->getPeriodeDeVol()->getDecollage()); //valeur decollage du vol d'origine

        if($isThisVolHaveNewPlane == true) { //si l'avion a changé
            $volNew->getPeriodeDeVol()->setAtterissage($newheureatterissage); //valeur atterissage du vol d'origine
        }else{ //si l'avion est identique
            $volNew->getPeriodeDeVol()->setAtterissage( $volOrigineCloneValeursFiges->getPeriodeDeVol()->getAtterissage()); //valeur atterissage du vol d'origine
        }

        //------------------------------------------------------------------------------------------
        //Pour un Vol New Verification de changement d'état en fonction des cas spéciaux de messages
        //------------------------------------------------------------------------------------------
        //$volNew->getPeriodeDeVol()->setEtat($this->getChangementSpeciauxDEtat($onViensDeCreerLeVol,$volNew,$volOrigineCloneValeursFiges));


        //---------------------------------------
        //Modification/Création du Vol historique
        //---------------------------------------

        //le vol d'origine a-t-il été acquité/envoyé au moins une fois?
        //-------------------------------------------------------------
        if( $onViensDeCreerLeVol == true){
            //---------------------------------------------------------------------
            //ce vol n'a pas encore été envoyé/acquité on ne créé pas d'historique,
            //---------------------------------------------------------------------

            //----------------------------------------
            //création d'un vol historique sans parent
            //----------------------------------------
            $volHistoriqueNew = VolHistorique::createFromVol($volNew);
            Vol::saveVolInHistorique($volHistoriqueNew,$em);
            $volNew->setVolHistoriqueParentable($volHistoriqueNew);
            $volHistoriqueNew->setVolHistorique($volNew);


        }else {
            //----------------------------
            //ce vol a déja été enregistré
            //----------------------------

            //--------------------------------------------------------------
            //création d'un vol historique avec pour parent le vol d'origine
            //--------------------------------------------------------------
            $volHistoriqueNew = VolHistorique::createFromVol($volNew);
            Vol::saveVolInHistorique($volHistoriqueNew,$em,$volOrigineCloneValeursFiges->getVolHistoriqueParentable());
            $volNew->setVolHistoriqueParentable($volHistoriqueNew);
            $volHistoriqueNew->setVolHistorique($volNew);

        }

        //----------------------------
        //on enregistre le nouveau vol
        //----------------------------
        $em->persist($volNew);


    }

    /**
     * Sauvegarde en attente des modification du vol d'origine
     */
    private function persistVolOrigineModified($volOrigineAModifier, $newAvion, $newPeriodeDebut, $newPeriodeFin, $newDecollage, $newAtterissage, $anewJoursDeValidites, $newEtat, $em){

        //TODO a modifier venderdi....

        //avion et compagnie
        if($volOrigineAModifier->getAvion() != $newAvion){

            //avion
            $volOrigineAModifier->setAvion($newAvion);

            //Compagnie
            $volOrigineAModifier->setCompagnie($newAvion->getCompagnie());
            if($newAvion->getCompagnie()->getCodeIATA() != "XK"){
                //Création d'un Clone du vol d'origine
                $monAffretement = $em->getRepository('AirCorsicaXKPlanBundle:Affretement')->find(3); //2 -> Affrètement technique, 3 -> Affrètement commercial
                $volOrigineAModifier->setAffretement($monAffretement);
            }else{
                $monAffretement = $em->getRepository('AirCorsicaXKPlanBundle:Affretement')->find(1); //1 -> Aucun
                $volOrigineAModifier->setAffretement($monAffretement);
            }



        }

        //on maj la période d'origine
        $volOrigineAModifier->getPeriodeDeVol()->setDateDebut($newPeriodeDebut);
        $volOrigineAModifier->getPeriodeDeVol()->setDateFin($newPeriodeFin);
        $volOrigineAModifier->getPeriodeDeVol()->setEtat($newEtat);
        $volOrigineAModifier->getPeriodeDeVol()->setJoursDeValidite($anewJoursDeValidites);
        $volOrigineAModifier->getPeriodeDeVol()->setDecollage($newDecollage);
        $volOrigineAModifier->getPeriodeDeVol()->setAtterissage($newAtterissage);

        //creation du vol historique
        $volHistoriqueNew = VolHistorique::createFromVol($volOrigineAModifier);
        Vol::saveVolInHistorique($volHistoriqueNew, $em, $volOrigineAModifier->getVolHistoriqueParentable());
        $volOrigineAModifier->setVolHistoriqueParentable($volHistoriqueNew);
        $volHistoriqueNew->setVolHistorique($volOrigineAModifier);

        //TODO attention ne pas oublié la modif des info du vol historique
        //exemple ancien code: maj du vol historique créé avec certaine ancienne valeurs necessaires
        //$volNewAvecNouvelAvion->getVolHistoriqueParentable()->setAvion($em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$i])->getAvion());//ancien avion
        //$volNewAvecNouvelAvion->getVolHistoriqueParentable()->setCompagnie($em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$i])->getCompagnie());//compagnie de l'ancien avion
        //$volNewAvecNouvelAvion->getVolHistoriqueParentable()->getPeriodeDeVol()->setDecollage($em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$i])->getPeriodeDeVol()->getDecollage());//ancienne heure de décollage
        //$volNewAvecNouvelAvion->getVolHistoriqueParentable()->getPeriodeDeVol()->setAtterissage($em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$i])->getPeriodeDeVol()->getAtterissage());//ancienne heure d'atterissage

        //on enregistre le nouveau vol
        $em->persist($volOrigineAModifier);

    }

    /**
     * Vérifications globales pour permettre ou non les modification d'avion demandées
     */
    private function isThisChangementAvionPourListeVolsSurPeriodePossible(Request $request,$arrayvolsid,$nouveauAvion,$ajourdevaliditeduchangement,$jourDebut,$jourfin,$em){

        $result = ['isValide'=>true,'code'=>'','message'=>''];
        $erreurtrouve = false;

        //Vérification que tous les jours sélectioné(lundi, jeudi, ...etc) existe dans la période de modification (cas période inférieur à une semaine)
        $averification_0 = $this->didAllSelectedDayExistOnceOnAModificationPeriod($nouveauAvion,$ajourdevaliditeduchangement,$jourDebut,$jourfin);
        if($averification_0['isValide'] == false){
            $erreurtrouve = true;
            $result['isValide'] = false;
            $result['code'] = 'error';
            $result['message'].= $averification_0['message'];
        }

        //Vérification que la période de modification soit incluse dans la période de vol
        $averification_1 = $this->didVolsPeriodIncludedInModificationPeriod($arrayvolsid,$jourDebut,$jourfin,$em);
        if($averification_1['isValide'] == false){
            if($erreurtrouve == true){
                $result['message'].= '@'.$averification_1['message'];
            }else{
                $erreurtrouve = true;
                $result['isValide'] = false;
                $result['code'] = 'error';
                $result['message'].= $averification_1['message'];
            }
        }

        //Vérification que tous les nouveau avion transmis ont bien leur durée de vol définis pour la période de vol initial inclus dans la période custom ou saison de modifications
//        $averification_2 = $this->didNewPlaneHasAVolDurationDefinedForAllInitialVolOnAPeriod($arrayvolsid,$nouveauAvion,$jourDebut,$jourfin,$em);
//        if($averification_2['isValide'] == false){
//            if($erreurtrouve == true){
//                $result['message'].= '@'.$averification_2['message'];
//            }else{
//                $erreurtrouve = true;
//                $result['isValide'] = false;
//                $result['code'] = 'error';
//                $result['message'].= $averification_2['message'];
//            }
//        }

        //Vérification que le nouvel avion à des horaires libres pour la modification sur la période de modifications transmise
        $averification_3 = $this->didNewPlaneHasSlotAvailableForAllInitialVolOnAPeriod($request,$arrayvolsid,$nouveauAvion,$ajourdevaliditeduchangement,$jourDebut,$jourfin,$em);
        if($averification_3['isValide'] == false){
            if($erreurtrouve == true){
                $result['message'].= '@'.$averification_3['message'];
            }else{
                $erreurtrouve = true;
                $result['isValide'] = false;
                $result['code'] = 'error';
                $result['message'].= $averification_3['message'];
            }
        }

        //Vérification que les jours de validité de la modifications transmis existent bien dans le vol initial
        $averification_4 = $this->didValidityDaysSelectedExistForAllInitialVol($arrayvolsid,$ajourdevaliditeduchangement,$em);
        if($averification_4['isValide'] == false){
            if($erreurtrouve == true){
                $result['message'].= '@'.$averification_4['message'];
            }else{
                $erreurtrouve = true;
                $result['isValide'] = false;
                $result['code'] = 'error';
                $result['message'].= $averification_4['message'];
            }
        }

        return $result;
    }

    /**
     *  Vérification que tous les jours de modification choisi existe au moin une fois dans la période de modification
     * (cad des période inférieur à une semaine cas d'une séléction Hebdomadaire sur une période)
     *
     * @param array $ajourdevaliditeduchangement
     * @param \DateTime $jourDebut
     * @param \DateTime $jourfin
     */
    private function didAllSelectedDayExistOnceOnAModificationPeriod($nouveauAvion,$ajourdevaliditeduchangement,$jourDebut,$jourfin)
    {

        $result = ['isValide' => true, 'code' => '', 'message' => ''];
        $days = array('0'=>'monday','1' => 'tuesday','2' => 'wednesday','3'=>'thursday','4' =>'friday','5' => 'saturday','6'=>'sunday');
        $aJours = array('0'=>'lundi','1' => 'mardi','2' => 'mercredi','3'=>'jeudi','4' =>'vendredi','5' => 'samedi','6'=>'dimanche');
        $lesJoursEnErreurs = "";
        $messagederreur = 'ERREUR D\'EXISTENCE DE JOUR: la changement d\'avion en '.$nouveauAvion->getNom().' ne peut être réalisé car le(s) jour(s) de la semaine: ';
        $erreurtrouve = false;

        if ( intval(date_diff($jourDebut, $jourfin)->format('%R%a') ) < 6 ){ //la période de modification est plus petite qu'une semaine

            $jourCourantTemp = clone $jourDebut;
            $nbDeJourDeModificationTransmis = count($ajourdevaliditeduchangement);

            //on transform l'associative array des jours a modifié en un indexed array
            $ajourTransmis = array();
            for($i=0;$i<count($ajourdevaliditeduchangement);$i++){
                $ajourTransmis[$ajourdevaliditeduchangement[$i]] = $ajourdevaliditeduchangement[$i];
            }


            //on parcour les jours de la période de modification
            $ajourExistantDansPeriode = array();
            for($i=0;$i<=intval(date_diff($jourDebut, $jourfin)->format('%R%a'));$i++){

                for($j=0;$j<count($ajourdevaliditeduchangement);$j++){
                    if((intval($ajourdevaliditeduchangement[$j])+1) == $jourCourantTemp->format('N')){
                        $ajourExistantDansPeriode[$ajourdevaliditeduchangement[$j]] = $ajourdevaliditeduchangement[$j]; //ce jour existe dans la période
                    }
                }

                $jourCourantTemp->modify('+1 days');
            }

            // on passe en revu les jours qui n'existent pas
            if(count($ajourExistantDansPeriode) != count($ajourdevaliditeduchangement)){
                $erreurtrouve = true;
                $ajoursquinexistentpas = array_diff_assoc($ajourTransmis,$ajourExistantDansPeriode);
                foreach ($ajoursquinexistentpas as $key => $value) {
                    $lesJoursEnErreurs.= $aJours[$value].', ';
                }
            }

        }


        if($erreurtrouve == true){

            $messagederreur.= $lesJoursEnErreurs.' n\'éxiste(nt) pas dans la période de modification => Vous devez d\'abors enlever le(s) jour(s) '.$lesJoursEnErreurs.' sur la periode de modification.';
            $result = ['isValide'=>false,'code'=>'error',"message"=>$messagederreur];

        }

        return $result;

    }


    /**
     * Vérification que la période de modification soit incluse dans la période de vol
     *
     * @param Array $arrayvolsid
     * @param \DateTime $jourDebut
     * @param \DateTime $jourfin
     *
     */
    private function didVolsPeriodIncludedInModificationPeriod($arrayvolsid,$jourDebut,$jourfin,$em){

        $result = ['isValide'=>true,'code'=>'','message'=>''];
        $messagederreur = 'ERREUR PERIODE: la période ou saison de modification est extérieure à la(aux) période(s) définie(s) pour le(s) vol(s): ';
        $erreurtrouve = false;

        for ($j = 0; $j < count($arrayvolsid); $j++) {

            //on récupére le vol d'origine
            $volCourant = $em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$j]);

            //--------------------------------------------------------------------------------
            //si la période de vol d'origine est exterieur à la période(saison) de mofication
            //--------------------------------------------------------------------------------

            if (
                (
                    (intval(date_diff($jourDebut, $volCourant->getPeriodeDeVol()->getDateDebut())->format('%R%a')) < 0) &&
                    (intval(date_diff($jourDebut, $volCourant->getPeriodeDeVol()->getDateFin())->format('%R%a')) < 0)
                ) || (
                    (intval(date_diff($jourfin, $volCourant->getPeriodeDeVol()->getDateDebut())->format('%R%a')) > 0)&&
                    (intval(date_diff($jourfin, $volCourant->getPeriodeDeVol()->getDateFin())->format('%R%a')) > 0)
                )
            ) {
                $erreurtrouve = true;
                $messagederreur .= $volCourant->getNumero().', ';
            }


        }

        if($erreurtrouve == true){

            $messagederreur.= ' => Vous devez d\'abors vérifier que le(s) vol(s) déplaçés sont bien définie(s) sur la periode ou saison de modification.';
            $result = ['isValide'=>false,'code'=>'error',"message"=>$messagederreur];

        }

        return $result;

    }

    /**
     * Vérification que tous les nouveau avion transmis ont bien leur durée de vol définis pour la période de vol initial inclus dans la période custom ou saison de modifications
     *
     * @param Array $arrayvolsid
     * @param Avion $nouveauAvion
     * @param \DateTime $jourDebut
     * @param \DateTime $jourfin
     *
     */
    private function didNewPlaneHasAVolDurationDefinedForAllInitialVolOnAPeriod($arrayvolsid,$nouveauAvion,$jourDebut,$jourfin,$em){

        $result = ['isValide'=>true,'code'=>'','message'=>''];
        $messagederreur = 'ERREUR TEMPS DE VOL: l\'avion cible selectionné: '.$nouveauAvion->getNom().', n\'a pas de durée de vol définie sur la(les) ligne(s): ';
        $lignesenerreurpournouvelavion = "";
        $alignesenerreur = array();

        for ($j = 0; $j < count($arrayvolsid); $j++) {

            //on récupére le vol d'origine
            $volOrigine = $em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$j]);

            //Recherche de la sous-période Vol d'origine inclue dans la saison(periode) transmise
            $debutPeriodeATester = $volOrigine->getPeriodeDeVol()->getDateDebut();
            $finPeriodeATester = $volOrigine->getPeriodeDeVol()->getDateFin();

            if (intval(date_diff($jourDebut, $volOrigine->getPeriodeDeVol()->getDateDebut())->format('%R%a')) < 0) {
                $debutPeriodeATester = $jourDebut;
            }
            if (intval(date_diff($volOrigine->getPeriodeDeVol()->getDateFin(), $jourfin)->format('%R%a')) < 0) {
                $finPeriodeATester = $jourfin;
            }

            if($volOrigine->getAvion()->getTypeAvion()->getCodeIATA() != $nouveauAvion->getTypeAvion()->getCodeIATA()){

                //recherche de la durée du vol pour ce nouvel avion sur la ligne de ce vol
                $nouvelledureeduvol = $this->getPlaneVolDurationForAVolForAPeriod($volOrigine,$nouveauAvion, $debutPeriodeATester, $finPeriodeATester,$em);

                //si la durée du vol est égale à 0 (erreur de saisie dans la base)
                if($nouvelledureeduvol == 0) {

                    // cas d'une erreur
                    $lignecourante = $volOrigine->getLigne()->getAeroportDepart()->getCodeIATA().'-'.$volOrigine->getLigne()->getAeroportArrivee()->getCodeIATA();

                    if( count($alignesenerreur) == 0 || !in_array($lignecourante,$alignesenerreur) ){
                        $lignesenerreurpournouvelavion.=$lignecourante.', ';
                        array_push($alignesenerreur,$lignecourante);
                    }

                }

            }

        }

        if( count($alignesenerreur) != 0){

            if ( intval(date_diff($jourDebut, $jourfin)->format('%R%a')) == 0 ){
                $echelledetemps="le jour";
            }else{
                $echelledetemps="la période";
            }

            $messagederreur.=$lignesenerreurpournouvelavion.' => Vous devez d\'abors définir un temps de vol pour cette(ces) ligne(s) avec ce type d\'avion pour '.$echelledetemps.' de modification.';
            $result = ['isValide'=>false,'code'=>'error',"message"=>$messagederreur];

        }

        return $result;
    }

    /**
     * Vérification que le nouvel avion à des horaires libres pour la modification sur la période de modifications transmise
     *
     * @param Array $arrayvolsid
     * @param Avion $nouveauAvion
     * @param \DateTime $nouvelleheureatterissage
     * @param array $ajourdevaliditeduchangement
     * @param \DateTime $jourDebut
     * @param \DateTime $jourfin
     *
     */
    private function didNewPlaneHasSlotAvailableForAllInitialVolOnAPeriod(Request $request, $arrayvolsid,$nouveauAvion,$ajourdevaliditeduchangement,$jourDebut,$jourfin,$em){

        $result = ['isValide'=>true,'code'=>'','message'=>''];
        $messagederreur = 'ERREUR D\'OCCUPATION: l\'avion cible selectionné: '.$nouveauAvion->getNom().', n\'est pas disponible le(s) ';
        $days = array('0'=>'monday','1' => 'tuesday','2' => 'wednesday','3'=>'thursday','4' =>'friday','5' => 'saturday','6'=>'sunday');
        $repoTemplate = $em->getRepository('AirCorsicaXKPlanBundle:Template');
        $templateCourant = $repoTemplate->find($request->getSession()->get('template'));
        $erreurtrouve = false;

        //pour chaque jour de validités (lundi, mercredi ...)
        for($i =0;$i<count($ajourdevaliditeduchangement);$i++){

            //recherche du veritabke $jourDebut en fonction du jour de validité courant de la boucle
            $iterationjour = clone $jourDebut;
            if( (intval($ajourdevaliditeduchangement[$i])+1) != $jourDebut->format('N')) {// si jour début n'est pas le même jour de la semaine que le jour de validité de la boucle, on cherche le suivant
                $iterationjour->modify('next ' . $days[$ajourdevaliditeduchangement[$i]]);
            }

            //pour chaque date incluse dans la période $jourDebut et $jourFin qui correspond au jour de validité courant
            //tant que le jour d'itération n'est pas plus grand que $datefin on execute le code
            while( intval(date_diff($iterationjour, $jourfin)->format('%R%a')) >= 0 ) {

                //pour chaque vol
                for ($j = 0; $j < count($arrayvolsid); $j++) {

                    //on récupére le vol courant à tester
                    $unVol = $em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$j]);

                    $heuredecollagevoldeplace = $unVol->getPeriodeDeVol()->getDecollage();

                    //on calcul la nouvelle heure d'atterissage
                    if ($unVol->getAvion()->getTypeAvion()->getCodeIATA() != $nouveauAvion->getTypeAvion()->getCodeIATA()) {
                        //recherche de la durée du vol pour ce nouvel avion sur la ligne de ce vol
                        $nouvelledureeduvol = $this->getPlaneVolDurationForAVolForAPeriod($unVol,$nouveauAvion, $jourDebut,$jourfin,$em);
                        //si la durée du vol est différente de 0 (erreur de saisie dans la base)
                        $nouvelleheureatterissage = clone $unVol->getPeriodeDeVol()->getDecollage();
                        if ($nouvelledureeduvol != 0) {
                            //on calcul la nouvelle heure d'arrivée
                            $nouvelleheureatterissage->modify('+' . $nouvelledureeduvol . ' minutes');
                        }else{
                            //valeur par défaut donné par AIR CORSICA lors de la 2em recette
                            $nouvelleheureatterissage->modify('+' . 59 . ' minutes');
                        }
                    }else{
                        $nouvelleheureatterissage = $unVol->getPeriodeDeVol()->getAtterissage();
                    }
                    $heureatterissagevoldeplace = $nouvelleheureatterissage;

                    //$joursdevalidites = ["0"=>"-","1"=>"-","2"=>"-","3"=>"-","4"=>"-","5"=>"-","6"=>"-"];
                    //$joursdevalidites[$iterationjour->format('N')-1] = $iterationjour->format('N');
                    $jourDeValiditeCourantDeLaBoucle = $iterationjour->format('N');

                    //testExistanceDeVolBloquants: on test si des vols existe déja sur cette plage horaire pour l'avion de remplacement (avion cible du déplacement)
                    $aexistanceDeVolBloquants = $em->getRepository('AirCorsicaXKPlanBundle:Vol')->getVolsDunAvionCeJourComprisDansPlageHoraire($unVol, $nouveauAvion, $iterationjour, $jourDeValiditeCourantDeLaBoucle, $heuredecollagevoldeplace, $heureatterissagevoldeplace, $templateCourant);

                    //si des vol sont retounré (array non vide) l'action n'est pas possible
                    if(count($aexistanceDeVolBloquants)!=0){
                        $erreurtrouve = true;
                        $dateenerreur = clone $iterationjour;
                        $messagederreur.= $dateenerreur->format('d-m-Y').' entre '.$unVol->getPeriodeDeVol()->getDecollage()->format('H:i').' et '.$heureatterissagevoldeplace->format('H:i').'(conflit avec '.$unVol->getNumero().'), ';
                    }
                }

                //on passe au jour suivant
                $iterationjour->modify('next ' . $days[$ajourdevaliditeduchangement[$i]]);

            }

        }

        if($erreurtrouve == true){

            // cas d'une erreur
            $messagederreur.=' => Vous devez d\'abors verifier que toutes les plages horaires des vols déplacés à la souris soient libre sur l\avion '.$nouveauAvion->getNom().' durant la période de modification.';
            $result = ['isValide'=>false,'code'=>'error',"message"=>$messagederreur];

        }

        return $result;
    }


    /**
     * Vérification que les jours de validité de la modifications transmis existent bien dans le vol initial
     *
     * @param Array $arrayvolsid
     * @param array $jourdevaliditeduchangement
     * @param entitymanager $em
     *
     */
    private function didValidityDaysSelectedExistForAllInitialVol($arrayvolsid,$ajourdevaliditeduchangement,$em){

        $result = ['isValide'=>true,'code'=>'','message'=>''];
        $daysinerror = array('0'=>'lundi','1' => 'mardi','2' => 'mercredi','3'=>'jeudi','4' =>'vendredi','5' => 'samedi','6'=>'dimanche');
        $messagederreur = 'ERREUR JOURS VALIDITES: les jours de validités selectionné pour ce changement d\'avion n\'existent pas pour: ';

        for ($j = 0; $j < count($arrayvolsid); $j++) {

            //on récupére le vol d'origine
            $volOrigine = $em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$j]);

            //on récupére les jours de validités de ce vol
            $ajoursDeValiditesduvolorigine = $volOrigine->getPeriodeDeVol()->getJoursDeValidite(); //du genre "0"=>"-","1"=>"-","2"=>"-","3"=>"-","4"=>"-","5"=>"-","6"=>"-"

            //on compare les 2 tableaux pour voir si $joursDeValiditesdelamodification est compris dans $ajoursDeValiditesduvolorigine?
            $erreurtrouve = false;
            $joursenerreurdecevol = "";

            for($i =0;$i<count($ajourdevaliditeduchangement);$i++){

                if($ajoursDeValiditesduvolorigine[$ajourdevaliditeduchangement[$i]] != intval($ajourdevaliditeduchangement[$i])+1){//ce jour de validité n'existe pas dans le vol d'origine

                    $erreurtrouve = true;
                    $joursenerreurdecevol.= $daysinerror[$ajourdevaliditeduchangement[$i]].' ';

                }

            }
            if($erreurtrouve == true){
                $messagederreur.= 'le(s) '.$joursenerreurdecevol.'sur '.$volOrigine->getNumero().', ';
            }

        }

        if($erreurtrouve == true){

            // cas d'une erreur
            $messagederreur.=' => Vous devez d\'abors verifier que tous les vol déplacés comportent ces jours de validités.';
            $result = ['isValide'=>false,'code'=>'error',"message"=>$messagederreur];

        }

        return $result;
    }




    /**
     * Pour une modification ponctuelle, fait la modification des vols déplaçè à la souris, retourne un message d'erreur ou ok à la methode ajax
     *
     * @param Request $request
     */
    public function savemodificationavionvolponctuelleAction(Request $request){

        //++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        //
        //              pour un jour courant unique
        //
        //++++++++++++++++++++++++++++++++++++++++++++++++++++++++

        //récupération des variables de sessions utilisés dans le panning de vol
        $session = $request->getSession();
        $templateid = $session->get('template'); //l'id du template ou repercuté les modifications

        //est-ce que l'opération est un déplacement à la souris ou une saisie dans le modal "modification ponctuelle"
        $appelMenuContextuel = $request->get('appelMenuContextuel');

        $result = '{"success":true}'; //ou $result = '{"success":false}' suivant le bon déroulement ou pas de l'enregistrement

        $arraydatesdelasemaine = $request->get('arraydatesdelasemaine'); //tableau de dates de la semaine ou la modification doit être réalisée
        $temp = explode('-',$arraydatesdelasemaine[0]);
        $dateJourStringUS = $temp[2].'-'.$temp[1].'-'.$temp[0];
        $dateJour = new \DateTime($dateJourStringUS);//on transforme en datetime la date au format texte
        $jouramodifier = intval($dateJour->format('N'));
        $arrayjoursdelasemaine = array($jouramodifier-1);

        $jour_val = array(1=>"-",2=>"-",3=>"-",4=>"-",5=>"-",6=>"-",7=>"-");

        $jour_val[$jouramodifier] = $jouramodifier;

        $arrayvolsid = $request->get('arrayvolsid'); //tableau d'id de vols à modifier

        $ancienavionid = $request->get('ancienavionid'); //l'id de l'avion avant le changement

        $nouvelavionid = $request->get('nouvelavionid'); //le nouvel id de l'avion affecté aux vols
        //on récupére un entity manager
        $em = $this->getDoctrine()->getManager();
        //on récupére le nouvel avion
        $nouveauAvion = $em->getRepository('AirCorsicaXKPlanBundle:Avion')->find($nouvelavionid);

        //on récupére le vol d'origine
        /** @var Vol $volOrigine */
//        $volOrigine = $em->getRepository('AirCorsicaXKPlanBundle:Vol')->find(current($arrayvolsid));

        //Heure du vol
        $changementheuredecollage = null;
        if( $request->request->has('changementheuredecollage') ){//cas du contexte menu "modification ponctuelle" du planning vol
            if( $request->get('changementheuredecollage') != 'false') {//l'heure de décollage a été changée (pour un jour unique)
                $changementheuredecollage = $request->get('changementheuredecollage');
            }
        }

        $changementheureatterissage = null;
        if( $request->request->has('changementheureatterissage') ){//cas du contexte menu "modification ponctuelle" du planning vol
            if( $request->get('changementheureatterissage') != 'false') {//l'heure d'atterissage a été changée (pour un jour unique)
                $changementheureatterissage = $request->get('changementheureatterissage');
            }
        }


        //la variable de vérification de faisabilité
        $modifsRealisables = false;
        //=====================================================================
        // vérification de la possibilité de réaliser la modification demandée
        //=====================================================================
        $verificationResult = $this->isThisChangementAvionPourListeVolsSurPeriodePossible($request,$arrayvolsid,$nouveauAvion,$arrayjoursdelasemaine,$dateJour,$dateJour,$em);

        if($verificationResult['isValide'] == true) {

            $modifsRealisables = true;
            $listeVolsDeplaces = "";

            //=======================================================================
            // Application des modifications demandées pour chaques vols sélectionés
            //=======================================================================

            //on passe en revu les vols à modifier
            for ($i = 0; $i < count($arrayvolsid); $i++) {
                //on récupére le vol d'origine
                /** @var Vol $volOrigine */
                $volOrigine = $em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$i]);

                //on récupére le numéro du vol courant  pour les messages générés à la fin de la modification
                $listeVolsDeplaces .= $volOrigine->getNumero() . ",";

                if($volOrigine->getAvion()->getId() != $nouvelavionid){
                    $request->request->add(array("choix_ponctuel" => "choix_avion"));
                    $request->request->add(array("avion_ponctuel" => $nouvelavionid));
                    if($changementheuredecollage || $changementheureatterissage) {
                        $request->request->add(array("heureminute_depart" => $changementheuredecollage));
                        $request->request->add(array("heureminute_arrivee" => $changementheureatterissage));
                        $request->request->add(array("planning" => 1));
                    }
                }elseif($changementheuredecollage || $changementheureatterissage){
                    $request->request->add(array("choix_ponctuel" => "choix_horaires"));
                    $request->request->add(array("heureminute_depart" => $changementheuredecollage));
                    $request->request->add(array("heureminute_arrivee" => $changementheureatterissage));
                }

                $request->request->add(array("debut_filtre" => $dateJourStringUS));
                $request->request->add(array("fin_filtre" => $dateJourStringUS));
                $request->request->add(array("ids_vol" => implode("_",$arrayvolsid)));
                $request->request->add(array("jour_val" => implode("_",$jour_val)));

                $response = $this->forward('AirCorsicaXKPlanBundle:Vol:updatemasse', array(
                    'request'  => $request,
                ));

                if($response->getStatusCode() < 400 && $response->getStatusCode() >= 200){
                    $result = '{"success":true}';
                }else{
                    $result = '{"success":false}';
                }

            }
        }

        //=========================================
        // Génération du message dans l'interface
        //=========================================

        if($modifsRealisables == true) { //succès de l'opèration

            //affichage du message
            $request->getSession()->getFlashBag()->add('success', 'Le(s) vol(s) '.$listeVolsDeplaces.' a(ont) été(s) déplaçé(s) sur l\'avion '.$nouveauAvion->getNom().' avec succés.');

        }else{ //impossibilité de réaliser l'opération(une ou plusieurs procèdure de vérification ont échouées)

            //on regarde si il y a plusieurs messages d'erreurs
            $arrayDeMessages = explode('@',$verificationResult['message']);
            if(count($arrayDeMessages)==0){
                $request->getSession()->getFlashBag()->add($verificationResult['code'], $verificationResult['message']);
            }else{
                foreach ($arrayDeMessages as $key => $value) {
                    $request->getSession()->getFlashBag()->add($verificationResult['code'], $value);
                }
            }

            //affichage du message
            $request->getSession()->getFlashBag()->add('warning', 'ATTENTION: Une(des) incohérence(s) a(ont) été(s) détectée(s), le changement d\'avion pur '.$nouveauAvion->getNom().' n\'a put être effectuée! => Faites les corrections ci-dessous avant de ré-essayer.');
        }

        return new Response(
            json_encode($result)
        );
    }



    /**
     * Pour une période, fait la modification des vols déplaçè à la souris, retourne un message d'erreur ou ok à la methode ajax
     *
     * @param Request $request
     */
    public function savemodificationavionvolsaisoncouranteoucustomperiodeAction(Request $request){

        //on récupére l'id du template ou repercuté les modifications
        $session = $request->getSession();
        $templateid = $session->get('template');

        //-----------------------------
        //on récupére un entity manager
        //-----------------------------
        $em = $this->getDoctrine()->getManager();

        //--------------------------
        //récupération des variables
        //--------------------------
        $periodesaisonid = $request->get('periodesaisonid'); //l'id de la période ou repercuté les modifications
        $periodesaisondebut = $request->get('periodesaisondebut'); //la date de debut de la période au format DD-MM-YYYY
        $periodesaisonfin = $request->get('periodesaisonfin'); //la date de fin de la période au format DD-MM-YYYY
        $arrayjoursdelasemaine = $request->get('arrayjoursdelasemaine'); //tableau de numéros de jours de la semaine ou la modification doit être réalisée (0:lundi ... 6:dimanche)
        $arrayvolsid = $request->get('arrayvolsid'); //tableau d'id de vols à modifier

        $jour_val = array(1=>"-",2=>"-",3=>"-",4=>"-",5=>"-",6=>"-",7=>"-");

        foreach ($arrayjoursdelasemaine as $jour){
            $jour_val[$jour+1] = $jour+1;
        }

        $ancienavionid = $request->get('ancienavionid'); //l'id de l'avion avant le changement
        $nouvelavionid = $request->get('nouvelavionid'); //le nouvel id de l'avion affecté aux vols

        //-----------------------------------------------------------
        //transformation de la période (custom ou saison) en DateTime
        //-----------------------------------------------------------
        $temp = explode('-',$periodesaisondebut);
        $saisonPeriodeDebutStringUS = $temp[2].'-'.$temp[1].'-'.$temp[0];
        $jourDebutSaison = new \DateTime($saisonPeriodeDebutStringUS);

        $temp = explode('-',$periodesaisonfin);
        $saisonPeriodeFinStringUS = $temp[2].'-'.$temp[1].'-'.$temp[0];
        $jourfinSaison = new \DateTime($saisonPeriodeFinStringUS);

        //-----------------------
        //définition de variables
        //-----------------------

        //la variable result
        $result = '{"success":true}'; //ou $result = '{"success":false}' suivant le bon déroulement ou pas de l'enregistrement

        //la variable de vérification de faisabilité
        $modifsRealisables = false;

        //--------------------------------------------------------
        //on récupére le nouvel avion ou les vols ont été déplacés
        //--------------------------------------------------------
        $nouveauAvion = $em->getRepository('AirCorsicaXKPlanBundle:Avion')->find($nouvelavionid);

        //=====================================================================
        // vérification de la possibilité de réaliser la modification demandée
        //=====================================================================
        $verificationResult = $this->isThisChangementAvionPourListeVolsSurPeriodePossible($request,$arrayvolsid,$nouveauAvion,$arrayjoursdelasemaine,$jourDebutSaison,$jourfinSaison,$em);

        if($verificationResult['isValide'] == true){

            $modifsRealisables = true;
            $listeVolsDeplaces = "";

            //=======================================================================
            // Application des modifications demandées pour chaques vols sélectionés
            //=======================================================================

            //on passe en revu les vols à modifier
            for ($i = 0; $i < count($arrayvolsid); $i++) {
                //on récupére le vol d'origine
                /** @var Vol $volOrigine */
                $volOrigine = $em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$i]);

                //on récupére le numéro du vol courant  pour les messages générés à la fin de la modification
                $listeVolsDeplaces .= $volOrigine->getNumero() . ",";

                if($volOrigine->getAvion()->getId() != $nouvelavionid){
                    $request->request->add(array("choix_ponctuel" => "choix_avion"));
                    $request->request->add(array("avion_ponctuel" => $nouvelavionid));
                }

                $request->request->add(array("debut_filtre" => $saisonPeriodeDebutStringUS));
                $request->request->add(array("fin_filtre" => $saisonPeriodeFinStringUS));
                $request->request->add(array("ids_vol" => $arrayvolsid[$i]));
                $request->request->add(array("jour_val" => implode("_",$jour_val)));

                $response = $this->forward('AirCorsicaXKPlanBundle:Vol:updatemasse', array(
                    'request'  => $request,
                ));

                if($response->getStatusCode() < 400 && $response->getStatusCode() >= 200){
                    $result = '{"success":true}';
                }else{
                    $result = '{"success":false}';
                }
            }
        }

        //=========================================
        // Génération du message dans l'interface
        //=========================================

        if($modifsRealisables == true) { //succès de l'opèration

            //affichage du message
            $request->getSession()->getFlashBag()->add('success', 'Le(s) vol(s) '.$listeVolsDeplaces.' a(ont) été(s) déplaçé(s) sur l\'avion '.$nouveauAvion->getNom().' avec succés.');

        }else{ //impossibilité de réaliser l'opération(une ou plusieurs procèdure de vérification ont échouées)

            //on regarde si il y a plusieurs messages d'erreurs
            $arrayDeMessages = explode('@',$verificationResult['message']);
            if(count($arrayDeMessages)==0){
                $request->getSession()->getFlashBag()->add($verificationResult['code'], $verificationResult['message']);
            }else{
                foreach ($arrayDeMessages as $key => $value) {
                    $request->getSession()->getFlashBag()->add($verificationResult['code'], $value);
                }
            }

            //affichage du message
            $request->getSession()->getFlashBag()->add('warning', 'ATTENTION: Une(des) incohérence(s) a(ont) été(s) détectée(s), le changement d\'avion pur '.$nouveauAvion->getNom().' n\'a put être effectuée! => Faites les corrections ci-dessous avant de ré-essayer.');
        }

        //==================
        // Retour du Json
        //==================

        return new Response(
            json_encode($result)
        );

    }


//    /**
//     * Pour une période, fait la modification des vols déplaçè à la souris, retourne un message d'erreur ou ok à la methode ajax
//     *
//     * @param Request $request
//     */
//    public function OLDsavemodificationavionvolsaisoncouranteoucustomperiodeAction(Request $request){
//
//        $result = '{"success":true}'; //ou $result = '{"success":false}' suivant le bon déroulement ou pas de l'enregistrement
//
//        //récupération des variables de sessions utilisés dans le panning de vol
//        $session = $request->getSession();
//
//        $templateid = $session->get('template'); //l'id du template ou repercuté les modifications
//        $periodesaisonid = $request->get('periodesaisonid'); //l'id de la période ou repercuté les modifications
//        $periodesaisondebut = $request->get('periodesaisondebut'); //la date de debut de la période au format DD-MM-YYYY
//        $periodesaisonfin = $request->get('periodesaisonfin'); //la date de fin de la période au format DD-MM-YYYY
//        $arrayjoursdelasemaine = $request->get('arrayjoursdelasemaine'); //tableau de numéros de jours de la semaine ou la modification doit être réalisée (0:lundi ... 6:dimanche)
//        $arrayvolsid = $request->get('arrayvolsid'); //tableau d'id de vols à modifier
//        $ancienavionid = $request->get('ancienavionid'); //l'id de l'avion avant le changement
//        $nouvelavionid = $request->get('nouvelavionid'); //le nouvel id de l'avion affecté aux vols
//
//        $temp = explode('-',$periodesaisondebut);
//        $saisonPeriodeDebutStringUS = $temp[2].'-'.$temp[1].'-'.$temp[0];
//        $jourDebutSaison = new \DateTime($saisonPeriodeDebutStringUS);
//
//        $temp = explode('-',$periodesaisonfin);
//        $saisonPeriodeFinStringUS = $temp[2].'-'.$temp[1].'-'.$temp[0];
//        $jourfinSaison = new \DateTime($saisonPeriodeFinStringUS);
//
//        //on récupére un entity manager
//        $em = $this->getDoctrine()->getManager();
//
//        //on récupére le nouvel avion
//        $nouveauAvion = $em->getRepository('AirCorsicaXKPlanBundle:Avion')->find($nouvelavionid);
//
//
//            //++++++++++++++++++++++++++++********************************
//            //
//            //               pour un jour courant unique
//            //
//            //++++++++++++++++++++++++++++********************************
//            if(count($arrayjoursdelasemaine)==1) {
//
//
//                //vérification que les nouveau avion transmis ont bien leur durée de vol définis pour la période de vol initial
//                //on passe en revu les vols à modifier
//                $modifvalide = true; //pour envoyer me message success
//                $messagederreur = '';
//                $testDureeVol = $this->didNewPlaneHasAVolDurationDefinedForAllInitialVolOnAPeriod($arrayvolsid,$nouveauAvion,$jourDebutSaison,$jourfinSaison,$em);
//                $modifvalide = $testDureeVol['isValide'];
//                $messagederreur = $testDureeVol['message'];
//
//
//                //si il n'y a pas d'erreur de validité,
//                if($modifvalide == true) {
//
//                    //on passe en revu les vols à modifier
//                    for ($i = 0; $i < count($arrayvolsid); $i++) {
//
//                        //on récupére le vol d'origine
//                        /** @var Vol $volOrigine */
//                        $volOrigine = $em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$i]);
//                        $periodeDebut_volOrigine = clone $volOrigine->getPeriodeDeVol()->getDateDebut();
//                        $periodeFin_volOrigine = clone $volOrigine->getPeriodeDeVol()->getDateFin();
//
//                        //-----------------------------------------------------------------------
//                        //Si l'avion d'origine n'est pas du même type que le nouvel avion
//                        //cad A320 au lieu d'un ATR, l'heure d'arrivée du vol vas être différente
//                        //-----------------------------------------------------------------------
//                        $modifierheurearrivee = false;
//                        if ($volOrigine->getAvion()->getTypeAvion()->getCodeIATA() != $nouveauAvion->getTypeAvion()->getCodeIATA()) {
//
//                            //Recherche de la sous-période Vol d'origine inclue dans la saison(periode) transmise
//                            $debutPeriodeATester = $volOrigine->getPeriodeDeVol()->getDateDebut();
//                            $finPeriodeATester = $volOrigine->getPeriodeDeVol()->getDateFin();
//                            if (intval(date_diff($jourDebutSaison, $volOrigine->getPeriodeDeVol()->getDateDebut())->format('%R%a')) < 0) {
//                                $debutPeriodeATester = $jourDebutSaison;
//                            }
//                            if (intval(date_diff($volOrigine->getPeriodeDeVol()->getDateFin(), $jourfinSaison)->format('%R%a')) < 0) {
//                                $finPeriodeATester = $jourfinSaison;
//                            }
//
//
//                            //recherche de la durée du vol pour ce nouvel avion sur la ligne de ce vol
//                            $nouvelledureeduvol = $this->getPlaneVolDurationForAVolForAPeriod($volOrigine,$nouveauAvion, $debutPeriodeATester,$finPeriodeATester,$em);
//
//                            //si la durée du vol est différente de 0 (erreur de saisie dans la base)
//                            if ($nouvelledureeduvol != 0) {
//                                //on doit modifier l'heure d'arrivée
//                                $modifierheurearrivee = true;
//                                //on calcul la nouvelle heure d'arrivée
//                                $nouvelleheureatterissage = clone $volOrigine->getPeriodeDeVol()->getDecollage();
//                                $nouvelleheureatterissage->modify('+' . $nouvelledureeduvol . ' minutes');
//                            }
//
//                        }
//
//
//                        //--------------------------------------------------------------------------------
//                        //si la période de vol d'origine est exterieur à la période(saison) de mofication
//                        //--------------------------------------------------------------------------------
//
//                        if (
//                            (
//                                (intval(date_diff($jourDebutSaison, $volOrigine->getPeriodeDeVol()->getDateDebut())->format('%R%a')) < 0) &&
//                                (intval(date_diff($jourDebutSaison, $volOrigine->getPeriodeDeVol()->getDateFin())->format('%R%a')) < 0)
//                            ) || (
//                                (intval(date_diff($jourfinSaison, $volOrigine->getPeriodeDeVol()->getDateDebut())->format('%R%a')) > 0)&&
//                                (intval(date_diff($jourfinSaison, $volOrigine->getPeriodeDeVol()->getDateFin())->format('%R%a')) > 0)
//                            )
//                        ) {
//
//                            //Rien a faire pour ce vol aucune modification possible la période de ce vol est à l'extérieur de la période de modification transmise
//                            if($modifvalide == true) {
//                                $messagederreur = 'Attention: les vols suivants n\'ont pas étés modifiés car leur période de vol n\'est pas incluse dans la période/saison de modification: ';
//                                $messagederreur .= $volOrigine->getNumero().' '.$volOrigine->getLigne()->getAeroportDepart()->getCodeIATA().'-'.$volOrigine->getLigne()->getAeroportArrivee()->getCodeIATA().' D'.$volOrigine->getPeriodeDeVol()->getDecollage()->format('H:i').' A'.$volOrigine->getPeriodeDeVol()->getAtterissage()->format('H:i');
//                                $errorcode = 'warning';
//                                $modifvalide = false;
//                            }else{
//                                $messagederreur = $messagederreur.', '.$volOrigine->getNumero().' '.$volOrigine->getLigne()->getAeroportDepart()->getCodeIATA().'-'.$volOrigine->getLigne()->getAeroportArrivee()->getCodeIATA().' D'.$volOrigine->getPeriodeDeVol()->getDecollage().' A'.$volOrigine->getPeriodeDeVol()->getAtterissage();
//                            }
//
//
//                        } else {
//
//
//
//                            //---------------------------------------------------------------------------------------------------------------------------
//                            //Les modifications ponctuelle sont à effectué sur la période commune à la période de vol et à la période(saison) de travail
//                            //Calculs de tableaux récapitulatifs pour effectuer ces modification
//                            //---------------------------------------------------------------------------------------------------------------------------
//
//                            //Recherche de la sous-période Vol d'origine inclue dans la saison(periode) transmise
//                            $debutPeriodeComuneATraiter = $volOrigine->getPeriodeDeVol()->getDateDebut();
//                            $finPeriodeComuneATraiter = $volOrigine->getPeriodeDeVol()->getDateFin();
//                            if (intval(date_diff($jourDebutSaison, $volOrigine->getPeriodeDeVol()->getDateDebut())->format('%R%a')) < 0) {
//                                $debutPeriodeComuneATraiter = $jourDebutSaison;
//                            }
//                            if (intval(date_diff($volOrigine->getPeriodeDeVol()->getDateFin(), $jourfinSaison)->format('%R%a')) < 0) {
//                                $finPeriodeComuneATraiter = $jourfinSaison;
//                            }
//
//                            //creation du tableau des jours avec modification ponctuelle
//                            $days = array('0'=>'monday','1' => 'tuesday','2' => 'wednesday','3'=>'thursday','4' =>'friday','5' => 'saturday','6'=>'sunday');
//                            $joursDeValidites = ["0"=>"-","1"=>"-","2"=>"-","3"=>"-","4"=>"-","5"=>"-","6"=>"-"];
//                            $joursDeValidites[$arrayjoursdelasemaine[0]] = intval($arrayjoursdelasemaine[0])+1;
//                            $arrayjours_avecchangement = array();
//
//                            $iterationjour = clone $debutPeriodeComuneATraiter;
//                            if( (intval($arrayjoursdelasemaine[0])+1) != $debutPeriodeComuneATraiter->format('N')) {
//                                $iterationjour->modify('next ' . $days[$arrayjoursdelasemaine[0]]);
//                            }
//
//                            while( (intval(date_diff($iterationjour, $finPeriodeComuneATraiter)->format('%R%a')) >= 0) ){
//
//                                array_push($arrayjours_avecchangement,array('debut' => clone $iterationjour, 'fin' => clone $iterationjour, 'joursvalidites' =>$joursDeValidites));
//
//                                $iterationjour->modify('+1 week');
//                            }
//
//                            //creation du tableau des sous période de x jours sans changement
//                            $arrayperiode_restantidentique = array();
//                            if(count($arrayjours_avecchangement)>1) {
//                                for ($compt = 0; $compt < (count($arrayjours_avecchangement)-1); $compt++) {
//
//                                    $datetempdebut = clone $arrayjours_avecchangement[$compt]['fin'];
//                                    $datetempdebut->modify('+1 day');
//                                    $datetempfin = clone $arrayjours_avecchangement[$compt+1]['debut'];
//                                    $datetempfin->modify('-1 day');
//                                    $arrayperiode_restantidentique[$compt] = array('debut' => $datetempdebut, 'fin' => $datetempfin);
//
//                                }
//                            }
//
//                            //---------------------------------------------------------
//                            //Ajout des période exterieur aux modifications ponctuelle
//                            //---------------------------------------------------------
//
//                            //calcul de la période sans changements, avant la première modification ponctuelle
//                            //date_diff(A,B)->format('%R%a') si B > A alors chiffre positif
//                            if(intval(date_diff($volOrigine->getPeriodeDeVol()->getDateDebut(), $arrayjours_avecchangement[0]['debut'])->format('%R%a')) > 0){
//
//                                //calcul de la nouvelle date de fin
//                                $dateTempFin = clone $arrayjours_avecchangement[0]['debut'];
//                                $dateTempFin->modify('-1 day');
//
//                                //on rajoute au début du tableau $arrayperiode_restantidentique cet enregistrement
//                                array_unshift($arrayperiode_restantidentique,array('debut' => $volOrigine->getPeriodeDeVol()->getDateDebut(), 'fin' => $dateTempFin));
//                            }
//
//                            //calcul de la période sans changements après la dernière modification ponctuelle
//                            //date_diff(A,B)->format('%R%a') si B > A alors chiffre positif
//                            if(intval(date_diff($arrayjours_avecchangement[count($arrayjours_avecchangement)-1]['fin'],$volOrigine->getPeriodeDeVol()->getDateFin())->format('%R%a')) > 0){
//
//                                //calcul de la nouvelle date de début
//                                $dateTempDebut = clone $arrayjours_avecchangement[count($arrayjours_avecchangement)-1]['fin'];
//                                $dateTempDebut->modify('+1 day');
//
//                                //on rajoute a la fin du tableau $arrayperiode_restantidentique cet enregistrement
//                                array_push($arrayperiode_restantidentique,array('debut' => $dateTempDebut, 'fin' => $volOrigine->getPeriodeDeVol()->getDateFin()));
//
//                            }
//
//
//                            //---------------------------------------------------------------------------------------------
//                            // Traitement de la période inclus entre le 1er et lde dernier changements ponctuel
//                            // Création des changements ponctuels et des periode restant identique entre chaque changement
//                            //---------------------------------------------------------------------------------------------
//
//                            //si il existe des jours avec des modifications ponctuelles à effectuer
//                            if(count($arrayjours_avecchangement)!=0) {
//
//                                //Changements ponctuels
//                                for ($cptNouveau = 0; $cptNouveau <= (count($arrayjours_avecchangement) - 1); $cptNouveau++) {
//
//                                    //Création d'un vol avec le même numéro de vol avec
//                                    //le nouvel avion transmis et
//                                    //une période Vol correspondant à la période de vol courante de la boucle
//                                    //-----------------------------------------------------------------------
//
//                                    $volUnePeriodeAvecNouvelAvion = clone $em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$i]);
//
//                                    //periode de vol
//                                    $periodevolNew = new PeriodeDeVol();
//                                    $periodevolNew->setDateDebut($arrayjours_avecchangement[$cptNouveau]['debut']);
//                                    $periodevolNew->setDateFin($arrayjours_avecchangement[$cptNouveau]['fin']);
//
//                                    if ( ($volOrigine->getAvion()->getTypeAvion()->getCodeIATA() != $nouveauAvion->getTypeAvion()->getCodeIATA()) || ($volOrigine->getAvion()->getCompagnie()->getCodeIATA() != $nouveauAvion->getCompagnie()->getCodeIATA()) ) {
//
//                                        // on effectue une modification ponctuelle
//                                        $volUnePeriodeAvecNouvelAvion->setModificationPonctuelle(1);
//
//                                        $periodevolNew->setEtat('pendingSend');
//                                    }else{
//                                        $periodevolNew->setEtat('send');
//                                    }
//
//                                    $volUnePeriodeAvecNouvelAvion->setAvion($nouveauAvion);
//                                    $volUnePeriodeAvecNouvelAvion->setCompagnie($nouveauAvion->getCompagnie());
//
//                                    //heure décollage et heure d'attérissage modifiée
//                                    $periodevolNew->setDecollage($volUnePeriodeAvecNouvelAvion->getPeriodeDeVol()->getDecollage()); //valeur decollage du vol d'origine
//                                    if ($modifierheurearrivee == true) {//il y a eu un changement de type avion
//                                        $periodevolNew->setAtterissage($nouvelleheureatterissage);
//                                    } else {
//                                        $periodevolNew->setAtterissage($volUnePeriodeAvecNouvelAvion->getPeriodeDeVol()->getAtterissage()); //valeur atterissage du vol d'origine
//                                    }
//                                    //jour de validité pour ce/ces jour/s
//                                    $periodevolNew->setJoursDeValidite($arrayjours_avecchangement[$cptNouveau]['joursvalidites']);
//                                    //on enregistre la nouvelle période de vol
//                                    $volUnePeriodeAvecNouvelAvion->setPeriodeDeVol($periodevolNew);
//                                    //creation du vol historique
//                                    $volHistoriqueNew = VolHistorique::createFromVol($volUnePeriodeAvecNouvelAvion);
//                                    //Vol::saveVolInHistorique($volHistoriqueNew, $em);
//                                    Vol::saveVolInHistorique($volHistoriqueNew, $em, $volOrigine->getVolHistoriqueParentable());
//                                    $volUnePeriodeAvecNouvelAvion->setVolHistoriqueParentable($volHistoriqueNew);
//                                    $volHistoriqueNew->setVolHistorique($volUnePeriodeAvecNouvelAvion);
//                                    //maj du vol historique créé avec certaine ancienne valeurs necessaires
//                                    $volUnePeriodeAvecNouvelAvion->getVolHistoriqueParentable()->setAvion($em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$i])->getAvion());//ancien avion
//                                    $volUnePeriodeAvecNouvelAvion->getVolHistoriqueParentable()->setCompagnie($em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$i])->getCompagnie());//compagnie de l'ancien avion
//                                    $volUnePeriodeAvecNouvelAvion->getVolHistoriqueParentable()->getPeriodeDeVol()->setDecollage($em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$i])->getPeriodeDeVol()->getDecollage());//ancienne heure de décollage
//                                    $volUnePeriodeAvecNouvelAvion->getVolHistoriqueParentable()->getPeriodeDeVol()->setAtterissage($em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$i])->getPeriodeDeVol()->getAtterissage());//ancienne heure d'atterissage
//
//                                    //on enregistre le nouveau vol
//                                    $em->persist($volUnePeriodeAvecNouvelAvion);
//
//                                }
//
//                                //periode ou le vol reste identique
//                                for ($cptIdentique = 0; $cptIdentique <= (count($arrayperiode_restantidentique) - 1); $cptIdentique++) {
//
//                                    if ($cptIdentique == 0) {
//
//                                        // Modification du vol d'origine et
//                                        //une période Vol correspondant à la première période de vol de la boucle
//                                        //------------------------------------------------------------------------
//
//                                        $volPeriodeAvionOrigine = $em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$i]);
//                                        //on modifie la période d'origine
//                                        $volPeriodeAvionOrigine->getPeriodeDeVol()->setDateDebut($arrayperiode_restantidentique[$cptIdentique]['debut']);
//                                        $volPeriodeAvionOrigine->getPeriodeDeVol()->setDateFin($arrayperiode_restantidentique[$cptIdentique]['fin']);
//
//                                        //creation du vol historique
//                                        $volHistoriqueNew = VolHistorique::createFromVol($volPeriodeAvionOrigine);
//                                        Vol::saveVolInHistorique($volHistoriqueNew, $em, $volOrigine->getVolHistoriqueParentable());
//                                        $volPeriodeAvionOrigine->setVolHistoriqueParentable($volHistoriqueNew);
//                                        $volHistoriqueNew->setVolHistorique($volPeriodeAvionOrigine);
//                                        //idem pour le vol historique
//                                        //$volPeriodeAvionOrigine->getVolHistoriqueParentable()->getPeriodeDeVol()->setDateDebut($arrayperiode_restantidentique[$cptIdentique]['debut']);
//                                        //$volPeriodeAvionOrigine->getVolHistoriqueParentable()->getPeriodeDeVol()->setDateFin($arrayperiode_restantidentique[$cptIdentique]['fin']);
//
//                                        //on enregistre le vol d'origine modifié
//                                        $em->persist($volPeriodeAvionOrigine);
//
//                                    } else {
//
//                                        //Création d'un vol avec le même numéro de vol avec l'avion d'origine et
//                                        //une période Vol correspondant à la période de vol courante de la boucle
//                                        //-----------------------------------------------------------------------
//
//                                        $volUnePeriodeAvecAvionOrigine = clone $em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$i]);
//                                        //periode de vol
//                                        $periodevolNew = new PeriodeDeVol();
//                                        $periodevolNew->setDateDebut($arrayperiode_restantidentique[$cptIdentique]['debut']);
//                                        $periodevolNew->setDateFin($arrayperiode_restantidentique[$cptIdentique]['fin']);
//                                        $periodevolNew->setEtat($volUnePeriodeAvecAvionOrigine->getPeriodeDeVol()->getEtat());
//                                        $periodevolNew->setJoursDeValidite($volUnePeriodeAvecAvionOrigine->getPeriodeDeVol()->getJoursDeValidite());
//                                        $periodevolNew->setDecollage($volUnePeriodeAvecAvionOrigine->getPeriodeDeVol()->getDecollage()); //valeur heure decollage du vol d'origine
//                                        $periodevolNew->setAtterissage($volUnePeriodeAvecAvionOrigine->getPeriodeDeVol()->getAtterissage()); //valeur heure atterissage du vol d'origine
//                                        //attribution de cette nouvelle période de vol
//                                        $volUnePeriodeAvecAvionOrigine->setPeriodeDeVol($periodevolNew);
//                                        //creation du vol historique
//                                        $volHistoriqueNew = VolHistorique::createFromVol($volUnePeriodeAvecAvionOrigine);
//                                        //Vol::saveVolInHistorique($volHistoriqueNew, $em);
//                                        Vol::saveVolInHistorique($volHistoriqueNew, $em, $volOrigine->getVolHistoriqueParentable());
//                                        $volUnePeriodeAvecAvionOrigine->setVolHistoriqueParentable($volHistoriqueNew);
//                                        $volHistoriqueNew->setVolHistorique($volUnePeriodeAvecAvionOrigine);
//                                        //on enregistre le nouveau vol
//                                        $em->persist($volUnePeriodeAvecAvionOrigine);
//
//                                    }
//
//                                }
//
//                            }
//
//
//                        }
//
//                        //on enregistre les modifications dans la BDD
//                        $em->flush();
//
//                    }
//
//                }
//
//            }else{
//
//
//                //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++******
//                //
//                //   pour une selection de plusieur jour de la semaine (lundi, jeudi, vendredi... etc)
//                //
//                //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++******
//
//                //vérification que le nouvel avion transmis ont bien leur durée de vol définis pour la période de vol initial
//                //on passe en revu les vols à modifier
//                $modifvalide = true; //pour envoyer me message success
//                $messagederreur = '';
//                $testDureeVol = $this->didNewPlaneHasAVolDurationDefinedForAllInitialVolOnAPeriod($arrayvolsid,$nouveauAvion,$jourDebutSaison,$jourfinSaison,$em);
//                $modifvalide = $testDureeVol['isValide'];
//                $messagederreur = $testDureeVol['message'];
//
//
//                //si il n'y a pas d'erreur de validité,
//                if($modifvalide == true) {
//
//                    //on passe en revu les vols à modifier
//                    for ($i = 0; $i < count($arrayvolsid); $i++) {
//
//                        //on récupére le vol d'origine
//                        /** @var Vol $volOrigine */
//                        $volOrigine = $em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$i]);
//                        $periodeDebut_volOrigine = clone $volOrigine->getPeriodeDeVol()->getDateDebut();
//                        $periodeFin_volOrigine = clone $volOrigine->getPeriodeDeVol()->getDateFin();
//
//                        //-----------------------------------------------------------------------
//                        //Si l'avion d'origine n'est pas du même type que le nouvel avion
//                        //cad A320 au lieu d'un ATR, l'heure d'arrivée du vol vas être différente
//                        //-----------------------------------------------------------------------
//                        $modifierheurearrivee = false;
//                        if ($volOrigine->getAvion()->getTypeAvion()->getCodeIATA() != $nouveauAvion->getTypeAvion()->getCodeIATA()) {
//
//                            //Recherche de la sous-période Vol d'origine inclue dans la saison(periode) transmise
//                            $debutPeriodeATester = $volOrigine->getPeriodeDeVol()->getDateDebut();
//                            $finPeriodeATester = $volOrigine->getPeriodeDeVol()->getDateFin();
//                            if (intval(date_diff($jourDebutSaison, $volOrigine->getPeriodeDeVol()->getDateDebut())->format('%R%a')) < 0) {
//                                $debutPeriodeATester = $jourDebutSaison;
//                            }
//                            if (intval(date_diff($volOrigine->getPeriodeDeVol()->getDateFin(), $jourfinSaison)->format('%R%a')) < 0) {
//                                $finPeriodeATester = $jourfinSaison;
//                            }
//
//                            //recherche de la durée du vol pour ce nouvel avion sur la ligne de ce vol
//                            $nouvelledureeduvol = $this->getPlaneVolDurationForAVolForAPeriod($volOrigine, $nouveauAvion, $debutPeriodeATester, $finPeriodeATester, $em);
//
//                            //si la durée du vol est différente de 0 (erreur de saisie dans la base)
//                            if ($nouvelledureeduvol != 0) {
//                                //on doit modifier l'heure d'arrivée
//                                $modifierheurearrivee = true;
//                                //on calcul la nouvelle heure d'arrivée
//                                $nouvelleheureatterissage = clone $volOrigine->getPeriodeDeVol()->getDecollage();
//                                $nouvelleheureatterissage->modify('+' . $nouvelledureeduvol . ' minutes');
//                            }
//
//                        }
//
//                            //--------------------------------------------------------------------------------
//                            //si la période de vol d'origine est exterieur à la période(saison) de mofication
//                            //--------------------------------------------------------------------------------
//                            if (
//                                (
//                                    (intval(date_diff($jourDebutSaison, $volOrigine->getPeriodeDeVol()->getDateDebut())->format('%R%a')) < 0) &&
//                                    (intval(date_diff($jourDebutSaison, $volOrigine->getPeriodeDeVol()->getDateFin())->format('%R%a')) < 0)
//                                ) || (
//                                    (intval(date_diff($jourfinSaison, $volOrigine->getPeriodeDeVol()->getDateDebut())->format('%R%a')) > 0)&&
//                                    (intval(date_diff($jourfinSaison, $volOrigine->getPeriodeDeVol()->getDateFin())->format('%R%a')) > 0)
//                                )
//                            ) {
//
//                                //Rien a faire pour ce vol aucune modification possible la période de ce vol est à l'extérieur de la période de modification transmise
//                                //date_diff(A,B)->format('%R%a') si B > A alors chiffre positif
//                                if ($modifvalide == true) {
//                                    $messagederreur = 'Attention: les vols suivants n\'ont pas étés modifiés car leur période de vol n\'est pas incluse dans la période/saison de modification: ';
//                                    $messagederreur .= $volOrigine->getNumero() . ' ' . $volOrigine->getLigne()->getAeroportDepart()->getCodeIATA() . '-' . $volOrigine->getLigne()->getAeroportArrivee()->getCodeIATA() . ' D' . $volOrigine->getPeriodeDeVol()->getDecollage()->format('H:i') . ' A' . $volOrigine->getPeriodeDeVol()->getAtterissage()->format('H:i');
//                                    $errorcode = 'warning';
//                                    $modifvalide = false;
//                                } else {
//                                    $messagederreur = $messagederreur . ', ' . $volOrigine->getNumero() . ' ' . $volOrigine->getLigne()->getAeroportDepart()->getCodeIATA() . '-' . $volOrigine->getLigne()->getAeroportArrivee()->getCodeIATA() . ' D' . $volOrigine->getPeriodeDeVol()->getDecollage() . ' A' . $volOrigine->getPeriodeDeVol()->getAtterissage();
//                                }
//
//
//                            } else {
//
//                                //---------------------------------------------------------------------------------------------------------------------------
//                                //Les modifications ponctuelle sont à effectué sur la période commune à la période de vol et à la période(saison) de travail
//                                //Calculs de tableaux récapitulatifs pour effectuer ces modification
//                                //---------------------------------------------------------------------------------------------------------------------------
//
//                                //Recherche de la sous-période Vol d'origine inclue dans la saison(periode) transmise
//                                $debutPeriodeComuneATraiter = $volOrigine->getPeriodeDeVol()->getDateDebut();
//                                $finPeriodeComuneATraiter = $volOrigine->getPeriodeDeVol()->getDateFin();
//                                if (intval(date_diff($jourDebutSaison, $volOrigine->getPeriodeDeVol()->getDateDebut())->format('%R%a')) < 0) {
//                                    $debutPeriodeComuneATraiter = $jourDebutSaison;
//                                }
//                                if (intval(date_diff($volOrigine->getPeriodeDeVol()->getDateFin(), $jourfinSaison)->format('%R%a')) < 0) {
//                                    $finPeriodeComuneATraiter = $jourfinSaison;
//                                }
//
//
//                                //creation du tableau des jours avec modification ponctuelle
//                                $days = array('0'=>'monday','1' => 'tuesday','2' => 'wednesday','3'=>'thursday','4' =>'friday','5' => 'saturday','6'=>'sunday');
//
//                                //valeur par defaut d'iterationjour
//                                $iterationjour = clone $debutPeriodeComuneATraiter;
//
//                                //initialisation variable
//                                $arrayperiodedelasemaine_avecchangement = array();
//                                $cptJrsSemEnTraitement = 0;
//                                $cptJrs = 0;
//                                $maxJrsSemATraiter = count($arrayjoursdelasemaine)-1;
//                                $debute = false;
//
//                                //recherche de sous période de X jours de changements dans l'array des jours de la semaine
//                                for($rch=0;$rch<=count($arrayjoursdelasemaine)-1;$rch++){
//                                    if($arrayjoursdelasemaine[$rch]+1 == $iterationjour->format('N')) {
//                                        $cptJrsSemEnTraitement = $rch;
//                                        $debute = true;
//                                        break;
//                                    } /*else if($arrayjoursdelasemaine[$rch]+1 > $iterationjour->format('N')){
//                                        $cptJrsSemEnTraitement = $rch;
//                                        break;
//                                    }*/
//                                }
//                                if($debute == false){
//                                    // ===============================================================================
//                                    // recherche du premier jour inclus a partir du début de la période de modification
//                                    // ===============================================================================
//                                    $temparrayrecherchepremierjour = array();
//                                    for($bozo=0;$bozo<=count($arrayjoursdelasemaine)-1;$bozo++){
//                                        $jouratester = clone $debutPeriodeComuneATraiter;
//                                        $jouratester->modify('next ' . $days[$arrayjoursdelasemaine[$bozo]]);
//                                        $temparrayrecherchepremierjour[$bozo] = $jouratester->getTimestamp();
//                                    }
//                                    //on cherche l'indice bozo qui correspond a la plus petite date
//                                    $pluspetitedateauformattimestamp = min($temparrayrecherchepremierjour);
//                                    $indicequelonrecherche = 0;
//                                    for($bozo=0;$bozo<=count($temparrayrecherchepremierjour)-1;$bozo++){
//                                        if($temparrayrecherchepremierjour[$bozo] == $pluspetitedateauformattimestamp){
//                                            $indicequelonrecherche = $bozo;
//                                            break;
//                                        }
//                                    }
//                                    //on passe au prochain jours a modifier
//                                    $iterationjour->modify('next ' . $days[$arrayjoursdelasemaine[$indicequelonrecherche]]);
//
//                                    $rch = $indicequelonrecherche;
//                                    $cptJrsSemEnTraitement = $rch;
//                                    //on passe au prochain jours a modifier
//                                    //$iterationjour->modify('next ' . $days[$arrayjoursdelasemaine[$cptJrsSemEnTraitement]]);
//                                }
//
//
//                                while( (intval(date_diff($iterationjour, $finPeriodeComuneATraiter)->format('%R%a')) >= 0) ) {
//
//                                    //on récupére le nombre de périodes déja enregistrées
//                                    $nbElmntsPeriodeSem = count($arrayperiodedelasemaine_avecchangement);
//
//                                    $iterationjourcourant = clone $iterationjour;
//
//                                    //on enregistre un element ou on modifie un ancien
//                                    if ($nbElmntsPeriodeSem == 0) {//le premier element
//                                        $arrayperiodedelasemaine_avecchangement[0] = array('debut' => $iterationjourcourant, 'fin' => $iterationjourcourant);
//                                    } else if (abs(intval(date_diff($iterationjour, $arrayperiodedelasemaine_avecchangement[$cptJrs - 1]['fin'])->format('%R%a'))) == 1) {//si la date suivante est ke jour suivant
//                                            $arrayperiodedelasemaine_avecchangement[$cptJrs - 1]['fin'] = $iterationjourcourant;
//                                            $cptJrs--;
//
//                                    }  else {//si il y a un espace entre les jours
//                                        $arrayperiodedelasemaine_avecchangement[$cptJrs] = array('debut' => $iterationjourcourant, 'fin' => $iterationjourcourant);
//                                    }
//
//                                    //incementation compteur jours de la semaine
//                                    $cptJrsSemEnTraitement++;
//                                    if ($cptJrsSemEnTraitement > $maxJrsSemATraiter) {
//                                        $cptJrsSemEnTraitement = 0;
//                                    }
//
//                                    $cptJrs++;
//
//                                    //on passe au prochain jours a modifier
//                                    $iterationjour->modify('next ' . $days[$arrayjoursdelasemaine[$cptJrsSemEnTraitement]]);
//                                }
//
//
//                                //on rajoute les jours de validité dans les éléments de ce tableau
//                                foreach($arrayperiodedelasemaine_avecchangement as $key => $uneperiode){
//
//                                    $jourcourantboucle = clone $uneperiode['debut'];
//                                    $joursDeValidites = ["0"=>"-","1"=>"-","2"=>"-","3"=>"-","4"=>"-","5"=>"-","6"=>"-"];
//
//                                    while(intval(date_diff($jourcourantboucle,$uneperiode['fin'])->format('%R%a')) >= 0){
//
//                                        $joursDeValidites[$jourcourantboucle->format('N')-1] = $jourcourantboucle->format('N');
//
//                                        $jourcourantboucle->modify('+1 day');
//                                    }
//
//                                    $arrayperiodedelasemaine_avecchangement[$key]['joursvalidites'] = $joursDeValidites;
//
//                                }
//
//                                //recherche de sous période de x jours sans changement
//                                $arrayperiodedelasemaine_restantidentique = array();
//                                if(count($arrayperiodedelasemaine_avecchangement)>1) {
//                                    for ($compt = 0; $compt < (count($arrayperiodedelasemaine_avecchangement)-1); $compt++) {
//
//                                        $datetempdebut = clone $arrayperiodedelasemaine_avecchangement[$compt]['fin'];
//                                        $datetempdebut->modify('+1 day');
//                                        $datetempfin = clone $arrayperiodedelasemaine_avecchangement[$compt+1]['debut'];
//                                        $datetempfin->modify('-1 day');
//                                        $arrayperiodedelasemaine_restantidentique[$compt] = array('debut' => $datetempdebut, 'fin' => $datetempfin);
//
//                                    }
//                                }
//
//
//                                //---------------------------------------------------------
//                                //Ajout des période exterieur aux modifications ponctuelle
//                                //---------------------------------------------------------
//
//                                //calcul de la période sans changements, avant la première modification ponctuelle
//                                //date_diff(A,B)->format('%R%a') si B > A alors chiffre positif
//                                if(intval(date_diff($volOrigine->getPeriodeDeVol()->getDateDebut(), $arrayperiodedelasemaine_avecchangement[0]['debut'])->format('%R%a')) > 0){
//
//                                    //calcul de la nouvelle date de fin
//                                    $dateTempFin = clone $arrayperiodedelasemaine_avecchangement[0]['debut'];
//                                    $dateTempFin->modify('-1 day');
//
//                                    //on rajoute au début du tableau $arrayperiode_restantidentique cet enregistrement
//                                    array_unshift($arrayperiodedelasemaine_restantidentique,array('debut' => $volOrigine->getPeriodeDeVol()->getDateDebut(), 'fin' => $dateTempFin));
//                                }
//
//                                //calcul de la période sans changements après la dernière modification ponctuelle
//                                //date_diff(A,B)->format('%R%a') si B > A alors chiffre positif
//                                if(intval(date_diff($arrayperiodedelasemaine_avecchangement[count($arrayperiodedelasemaine_avecchangement)-1]['fin'],$volOrigine->getPeriodeDeVol()->getDateFin())->format('%R%a')) > 0){
//
//                                    //calcul de la nouvelle date de début
//                                    $dateTempDebut = clone $arrayperiodedelasemaine_avecchangement[count($arrayperiodedelasemaine_avecchangement)-1]['fin'];
//                                    $dateTempDebut->modify('+1 day');
//
//                                    //on rajoute a la fin du tableau $arrayperiode_restantidentique cet enregistrement
//                                    array_push($arrayperiodedelasemaine_restantidentique,array('debut' => $dateTempDebut, 'fin' => $volOrigine->getPeriodeDeVol()->getDateFin()));
//
//                                }
//
//
//                                //---------------------------------------------------------------------------------------------
//                                // Traitement de la période inclus entre le 1er et lde dernier changements ponctuel
//                                // Création des changements ponctuels et des periode restant identique entre chaque changement
//                                //---------------------------------------------------------------------------------------------
//
//                                //si il existe des jours avec des modifications ponctuelles à effectuer
//                                if(count($arrayperiodedelasemaine_avecchangement)!=0) {
//
//                                    //Changements ponctuels
//                                    for ($cptNouveau = 0; $cptNouveau <= (count($arrayperiodedelasemaine_avecchangement) - 1); $cptNouveau++) {
//
//                                        //Création d'un vol avec le même numéro de vol avec
//                                        //le nouvel avion transmis et
//                                        //une période Vol correspondant à la période de vol courante de la boucle
//                                        //-----------------------------------------------------------------------
//
//                                        $volUnePeriodeAvecNouvelAvion = clone $em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$i]);
//
//                                        //periode de vol
//                                        $periodevolNew = new PeriodeDeVol();
//                                        $periodevolNew->setDateDebut($arrayperiodedelasemaine_avecchangement[$cptNouveau]['debut']);
//                                        $periodevolNew->setDateFin($arrayperiodedelasemaine_avecchangement[$cptNouveau]['fin']);
//
//                                        if ( ($volOrigine->getAvion()->getTypeAvion()->getCodeIATA() != $nouveauAvion->getTypeAvion()->getCodeIATA()) || ($volOrigine->getAvion()->getCompagnie()->getCodeIATA() != $nouveauAvion->getCompagnie()->getCodeIATA()) ) {
//
//                                            // on effectue une modification ponctuelle
//                                            $volUnePeriodeAvecNouvelAvion->setModificationPonctuelle(1);
//
//                                            $periodevolNew->setEtat('pendingSend');
//                                        }else{
//                                            $periodevolNew->setEtat('send');
//                                        }
//
//                                        $volUnePeriodeAvecNouvelAvion->setAvion($nouveauAvion);
//                                        $volUnePeriodeAvecNouvelAvion->setCompagnie($nouveauAvion->getCompagnie());
//
//                                        //heure décollage et heure d'attérissage modifiée
//                                        $periodevolNew->setDecollage($volUnePeriodeAvecNouvelAvion->getPeriodeDeVol()->getDecollage()); //valeur decollage du vol d'origine
//                                        if ($modifierheurearrivee == true) {//il y a eu un changement de type avion
//                                            $periodevolNew->setAtterissage($nouvelleheureatterissage);
//                                        } else {
//                                            $periodevolNew->setAtterissage($volUnePeriodeAvecNouvelAvion->getPeriodeDeVol()->getAtterissage()); //valeur atterissage du vol d'origine
//                                        }
//                                        //jour de validité pour ce/ces jour/s
//                                        $periodevolNew->setJoursDeValidite($arrayperiodedelasemaine_avecchangement[$cptNouveau]['joursvalidites']);
//                                        //on enregistre la nouvelle période de vol
//                                        $volUnePeriodeAvecNouvelAvion->setPeriodeDeVol($periodevolNew);
//                                        //creation du vol historique
//                                        $volHistoriqueNew = VolHistorique::createFromVol($volUnePeriodeAvecNouvelAvion);
//                                        //Vol::saveVolInHistorique($volHistoriqueNew, $em);
//                                        Vol::saveVolInHistorique($volHistoriqueNew, $em, $volOrigine->getVolHistoriqueParentable());
//                                        $volUnePeriodeAvecNouvelAvion->setVolHistoriqueParentable($volHistoriqueNew);
//                                        $volHistoriqueNew->setVolHistorique($volUnePeriodeAvecNouvelAvion);
//                                        //maj du vol historique créé avec certaine ancienne valeurs necessaires
//                                        $volUnePeriodeAvecNouvelAvion->getVolHistoriqueParentable()->setAvion($em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$i])->getAvion());//ancien avion
//                                        $volUnePeriodeAvecNouvelAvion->getVolHistoriqueParentable()->setCompagnie($em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$i])->getCompagnie());//compagnie de l'ancien avion
//                                        $volUnePeriodeAvecNouvelAvion->getVolHistoriqueParentable()->getPeriodeDeVol()->setDecollage($em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$i])->getPeriodeDeVol()->getDecollage());//ancienne heure de décollage
//                                        $volUnePeriodeAvecNouvelAvion->getVolHistoriqueParentable()->getPeriodeDeVol()->setAtterissage($em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$i])->getPeriodeDeVol()->getAtterissage());//ancienne heure d'atterissage
//
//                                        //on enregistre le nouveau vol
//                                        $em->persist($volUnePeriodeAvecNouvelAvion);
//
//                                    }
//
//                                    //periode ou le vol reste identique
//                                    for ($cptIdentique = 0; $cptIdentique <= (count($arrayperiodedelasemaine_restantidentique) - 1); $cptIdentique++) {
//
//                                        if ($cptIdentique == 0) {
//
//                                            // Modification du vol d'origine et
//                                            //une période Vol correspondant à la première période de vol de la boucle
//                                            //------------------------------------------------------------------------
//
//                                            $volPeriodeAvionOrigine = $em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$i]);
//                                            //on modifie la période d'origine
//                                            $volPeriodeAvionOrigine->getPeriodeDeVol()->setDateDebut($arrayperiodedelasemaine_restantidentique[$cptIdentique]['debut']);
//                                            $volPeriodeAvionOrigine->getPeriodeDeVol()->setDateFin($arrayperiodedelasemaine_restantidentique[$cptIdentique]['fin']);
//
//                                            //creation du vol historique
//                                            $volHistoriqueNew = VolHistorique::createFromVol($volPeriodeAvionOrigine);
//                                            Vol::saveVolInHistorique($volHistoriqueNew, $em, $volOrigine->getVolHistoriqueParentable());
//                                            $volPeriodeAvionOrigine->setVolHistoriqueParentable($volHistoriqueNew);
//                                            $volHistoriqueNew->setVolHistorique($volPeriodeAvionOrigine);
//                                            //idem pour le vol historique
//                                            //$volPeriodeAvionOrigine->getVolHistoriqueParentable()->getPeriodeDeVol()->setDateDebut($arrayperiodedelasemaine_restantidentique[$cptIdentique]['debut']);
//                                            //$volPeriodeAvionOrigine->getVolHistoriqueParentable()->getPeriodeDeVol()->setDateFin($arrayperiodedelasemaine_restantidentique[$cptIdentique]['fin']);
//
//                                            //on enregistre le vol d'origine modifié
//                                            $em->persist($volPeriodeAvionOrigine);
//
//                                        } else {
//
//                                            //Création d'un vol avec le même numéro de vol avec l'avion d'origine et
//                                            //une période Vol correspondant à la période de vol courante de la boucle
//                                            //-----------------------------------------------------------------------
//
//                                            $volUnePeriodeAvecAvionOrigine = clone $em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($arrayvolsid[$i]);
//                                            //periode de vol
//                                            $periodevolNew = new PeriodeDeVol();
//                                            $periodevolNew->setDateDebut($arrayperiodedelasemaine_restantidentique[$cptIdentique]['debut']);
//                                            $periodevolNew->setDateFin($arrayperiodedelasemaine_restantidentique[$cptIdentique]['fin']);
//                                            $periodevolNew->setEtat($volUnePeriodeAvecAvionOrigine->getPeriodeDeVol()->getEtat());
//                                            $periodevolNew->setJoursDeValidite($volUnePeriodeAvecAvionOrigine->getPeriodeDeVol()->getJoursDeValidite());
//                                            $periodevolNew->setDecollage($volUnePeriodeAvecAvionOrigine->getPeriodeDeVol()->getDecollage()); //valeur heure decollage du vol d'origine
//                                            $periodevolNew->setAtterissage($volUnePeriodeAvecAvionOrigine->getPeriodeDeVol()->getAtterissage()); //valeur heure atterissage du vol d'origine
//                                            //attribution de cette nouvelle période de vol
//                                            $volUnePeriodeAvecAvionOrigine->setPeriodeDeVol($periodevolNew);
//                                            //creation du vol historique
//                                            $volHistoriqueNew = VolHistorique::createFromVol($volUnePeriodeAvecAvionOrigine);
//                                            //Vol::saveVolInHistorique($volHistoriqueNew, $em);
//                                            Vol::saveVolInHistorique($volHistoriqueNew, $em, $volOrigine->getVolHistoriqueParentable());
//                                            $volUnePeriodeAvecAvionOrigine->setVolHistoriqueParentable($volHistoriqueNew);
//                                            $volHistoriqueNew->setVolHistorique($volUnePeriodeAvecAvionOrigine);
//                                            //on enregistre le nouveau vol
//                                            $em->persist($volUnePeriodeAvecAvionOrigine);
//
//                                        }
//
//                                    }
//
//                                }
//
//                            }
//
//                            //on enregistre les modifications dans la BDD
//                            $em->flush();
//
//                        //}
//
//                    }
//
//                }
//
//
//            }
//
//        //ajout du message de succés
//        if($modifvalide == true) {
//            $request->getSession()->getFlashBag()->add('success', 'Vol(s) déplaçé(s) sur l\'avion '.$nouveauAvion->getNom().' avec succés.');
//        }else{
//            $request->getSession()->getFlashBag()->add($errorcode, $messagederreur);
//        }
//
//        return new Response(
//            json_encode($result)
//        );
//
//
//    }




    public function whatEnglishDayNameForInt($numero){
        $englishName ="";
        switch ($numero) {
            case 0:
                //lundi
                $englishName = 'monday';
                break;
            case 1:
                //mardi
                $englishName = 'tuesday';
                break;
            case 2:
                //mercredi
                $englishName = 'wednesday';
                break;
            case 3:
                //jeudi
                $englishName = 'thursday';
                break;
            case 4:
                //vendredi
                $englishName = 'friday';
                break;
            case 5:
                //samedi
                $englishName = 'saturday';
                break;
            case 6:
                //dimanche
                $englishName = 'sunday';
                break;
        }

        return $englishName;

    }


}
