<?php
/**
 * Created by PhpStorm.
 * User: JeanJo
 * Date: 23/03/2017
 * Time: 09:06
 */

namespace AirCorsica\XKPlanBundle\Entity\Message;

use AirCorsica\XKPlanBundle\Entity\Message\ASMSSMMessage;
use AirCorsica\XKPlanBundle\Entity\Message\ASMSSMHeader;
use AirCorsica\XKPlanBundle\Entity\Message\ASMSSMAction;
use AirCorsica\XKPlanBundle\Entity\Message\ASMSSMFooter;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * SCR
 */
class ASMSSM implements InterfaceMessage
{

    private $container;

    private $em;

    /**
     * SCR constructor.
     * @param \AirCorsica\XKPlanBundle\Entity\Vol $vol
     * @param \Doctrine\ORM\EntityManager $em
     * @param $container
     */
    function __construct($em,$container)
    {
        //l'entity manager
        $this->em = $em;

        //le container
        $this->container = $container;

    }

//    /**
//     * @inheritdoc
//     */
//    //TODO une fois les AQMSSM fais. regarder les différences de la partie analyse de cette méthode (dans SCR et ASMSSM)
//    //TODO déplacer la partie de ce code et en faire une méthode générique dans l'entity vol; genre: analyseVolAnsFindMessageDataToSend(vol)
//    public function necessitemessages(\AirCorsica\XKPlanBundle\Entity\Vol $vol){
//
//        //toujours comparé xe que les GDS connaissent cad: le dernier état du vol de l'historique envoyé
//        //et l'etat du vol courant cad l'objet vol = le dernier enfant de l'historique
//        //on envoi uniquement la différence entre les deux
//
//        //2 cas:
//        // -> soit la periode vol est en état pendingcancel et c'est un message ASM ou SSM de Delestage qu'il faut envoyer
//        // -> soit la periode vol est en pendingsend et les différences sont à analyser pour en déduire les message ASM ou SSM à envoyer
//
//        $datamessagetogenerate = array();
//        $achangements = array();
//        $aligne = array();
//        $ajoursdevalidite = array();
//        $aperiodes = array();
//        $typeDeMessageASMSSM = '';
//
//        // -------------------------------------------------------------
//        // on récupére le dernier vol de l'historique ayant l'état send
//        // -------------------------------------------------------------
//
//        //TODO transformer cette algo en méthode dans la calsse volHistorique
//        $volhistoriqueachercher = $vol->getVolHistoriqueParentable();
//
//        while(true){
//
//            $parent = $volhistoriqueachercher->getParent();
//
//            if($parent==null){
//                $dernierVolDeHistoriqueAEtatConnu = null;
//                break;
//            }
//
//            $etat = $parent->getPeriodeDeVol()->getEtat();
//            if($etat=='send'){
//                $dernierVolDeHistoriqueAEtatConnu = $parent;
//                break;
//            }
//
//            $volhistoriqueachercher = $parent;
//
//        }
//
//        // on test si la péiode d'annulation est supérieur à 5 jours (génération d'un SSM)
//        // ou inférieur à 5 jours (génération d'un ASM)
//        $dateDebutPeriodeVol = $vol->getPeriodeDeVol()->getDateDebut()/*->format('Y-m-d')*/;
//        $dateFinPeriodeVol = $vol->getPeriodeDeVol()->getDateFin()/*->format('Y-m-d')*/;
//        $interval = $dateDebutPeriodeVol->diff($dateFinPeriodeVol);
//        if( intval($interval->format('%R%a')) > 5 ){
//            $typeDeMessageASMSSM = 'SSM';
//        }else{
//            $typeDeMessageASMSSM = 'ASM';
//        }
//
//        // Est-ce que le vol transmis est en pendingCancel?
//        if( $vol->getPeriodeDeVol()->getEtat() == 'pendingCancel'){
//            // le pendingCancel concerne des vol à supprimer (supression de type periode/jour/ligne...
//
//            $datamessagetogenerate[$typeDeMessageASMSSM]['action'] = 'CNL';
//            $datamessagetogenerate['volatraiter'] = $vol;
//
//
//        }else if( $vol->getPeriodeDeVol()->getEtat() == 'pendingSend'){// Quelle(s) message(s) doit ont générer
//
//            //On va comparer le vol $vol passé en paramètre avec le dernier vol àun état connu $dernierVolDeHistoriqueAEtatConnu et en déduire les messages à générer
//
//            //========================================================================
//            // Recherche des différences entre le vol fournis et le dernier vol connu
//            //========================================================================
//
//            //--------------------------------------------------------------------------------------------
//            // Recherche des changements autres que la période de vol, la ligne et les jours de validités
//            //--------------------------------------------------------------------------------------------
//            // si il y a uniquement des changements
//            // => ACTION UNIQUE (EQT, TIM, ADM) sinon RPL
//
//            // Nature de vol (Ne génére pas de ASM/SSM ??)
//            // Numéro  (Ne peut pas etre modifier donc pas de ASM/SSM)
//            // Affretement (Ne génére pas de ASM/SSM ??)
//            // Commentaire (Ne génére pas de ASM/SSM ??)
//
//            $actionDeduiteChangements = '';
//
//            // CodeShare
//            // est-ce qu'au moins un codeShare du vol n'existe pas dans le volhistorique
//            $nouveauCodeShareDuVolQuiNexistePasDansVolHistorique = false;
//            foreach( $vol->getCodesShareVol() as $unCodeShare ){
//                $trouve = false;
//                foreach( $dernierVolDeHistoriqueAEtatConnu->getCodesShareVol() as $unCodeShareHistorique){
//                    if($unCodeShareHistorique == $unCodeShare){
//                        $trouve = true;
//                    }
//                }
//                if($trouve == false){
//                    $nouveauCodeShareDuVolQuiNexistePasDansVolHistorique = true;
//                }
//            }
//
//            // est-ce que le nombre de codeshare est identique entre le vol et le volhistorique
//            $nombreDeCodeShareIdentiqueEntreVoletVolHistorique = true;
//            if(count($vol->getCodesShareVol()->toArray()) != count($dernierVolDeHistoriqueAEtatConnu->getCodesShareVol()->toArray())){
//                $nombreDeCodeShareIdentiqueEntreVoletVolHistorique = false;
//            }
//
//            // Code Share (action ADM si seul changement)
//            if(($nombreDeCodeShareIdentiqueEntreVoletVolHistorique == false) || ($nouveauCodeShareDuVolQuiNexistePasDansVolHistorique == true)){
//                $achangements['codeshare'] = $vol->getCodesShareVol();
//                $actionDeduiteChangements .='ADM_';
//            }
///*
//            // Code Share (action ADM si seul changement)
//            $aNouveauCodeShareDuVolQuiNexistePasDansVolHistorique = array_diff ($vol->getCodesShareVol()->toArray(),$dernierVolDeHistoriqueAEtatConnu->getCodesShareVol()->toArray());
//            $aCodeShareDuVolHistoriqueSupprimeQuiNexistePlusDansVol = array_diff ($dernierVolDeHistoriqueAEtatConnu->getCodesShareVol()->toArray(),$vol->getCodesShareVol()->toArray());
//            if( (count($aNouveauCodeShareDuVolQuiNexistePasDansVolHistorique) !=0) || (count($aCodeShareDuVolHistoriqueSupprimeQuiNexistePlusDansVol) !=0) ){
//                $achangements['codeshare'] = $vol->getCodesShareVol();
//                $actionDeduiteChangements .='ADM_';
//            }
//*/
//
//            // Compagnie (action EQT si seul changement)
//            if( $vol->getCompagnie() != $dernierVolDeHistoriqueAEtatConnu->getCompagnie() ){
//                $achangements['compagnie'] = $vol->getCompagnie();
//                $actionDeduiteChangements .='EQT_';
//            }
//
//            // ServiceType du vol (action EQT si seul changement)
//            if( $vol->getTypeDeVol()->getCodeService() != $dernierVolDeHistoriqueAEtatConnu->getTypeDeVol()->getCodeService() ){
//                $achangements['service'] = $vol->getTypeDeVol()->getCodeService();
//                $actionDeduiteChangements .='EQT_';
//            }
//
//            // Type d'Avion (action EQT si seul changement)
//            if( $vol->getAvion()->getTypeAvion()->getCodeIATA() != $dernierVolDeHistoriqueAEtatConnu->getAvion()->getTypeAvion()->getCodeIATA() ){
//                //on récupére le nombre de siéges -3chiffres- suivit du IATA aircraft subtype - 3 alphanumeric - )
//                $capaciteMsg = sprintf("%'.03d\n", $vol->getAvion()->getTypeAvion()->getCapaciteSiege()); //on format le retour pour toujour avoir 3 chiffes cad 30 -> 030 et 2 -> 002
//                $achangements['avion'] = $vol->getAvion()->getTypeAvion()->getCodeIATA().$capaciteMsg;
//                $actionDeduiteChangements .='EQT_';
//            }
//
//            //required arrival time (action TIM si seul changement)
//            $datetimeFormat = 'Hi';
//            if( ($vol->getPeriodeDeVol()->getAtterissage()->format($datetimeFormat) != $dernierVolDeHistoriqueAEtatConnu->getPeriodeDeVol()->getAtterissage()->format($datetimeFormat)) || ($vol->getPeriodeDeVol()->getDecollage()->format($datetimeFormat) != $dernierVolDeHistoriqueAEtatConnu->getPeriodeDeVol()->getDecollage()->format($datetimeFormat)) ) {
//
//                if ($vol->getPeriodeDeVol()->getAtterissage()->format($datetimeFormat) != $dernierVolDeHistoriqueAEtatConnu->getPeriodeDeVol()->getAtterissage()->format($datetimeFormat)) {
//                    $achangements['time']['arrival'] = $vol->getPeriodeDeVol()->getAtterissage()->format($datetimeFormat);
//                }
//
//                //require departure time
//                $datetimeFormat = 'Hi';
//                if ($vol->getPeriodeDeVol()->getDecollage()->format($datetimeFormat) != $dernierVolDeHistoriqueAEtatConnu->getPeriodeDeVol()->getDecollage()->format($datetimeFormat)) {
//                    $achangements['time']['departure'] = $vol->getPeriodeDeVol()->getDecollage()->format($datetimeFormat);
//                }
//
//                $actionDeduiteChangements .='TIM_';
//            }
//
//            $aactionDeduiteChangements = explode("_",$actionDeduiteChangements);
//
//
//            //-----------------------------------------
//            // Recherche des modifications de la ligne
//            //-----------------------------------------
//            // si il y a une modification de ligne
//            // => ACTION NEW
//
//            if( ($vol->getLigne()->getAeroportArrivee()->getCodeIATA() != $dernierVolDeHistoriqueAEtatConnu->getLigne()->getAeroportArrivee()->getCodeIATA()) || ($vol->getLigne()->getAeroportDepart()->getCodeIATA() != $dernierVolDeHistoriqueAEtatConnu->getLigne()->getAeroportDepart()->getCodeIATA()) ){
//                $aligne['Vol']['aeroportArrivee'] = $vol->getLigne()->getAeroportArrivee()->getCodeIATA();
//                $aligne['Vol']['aeroportDepart'] = $vol->getLigne()->getAeroportDepart()->getCodeIATA();
//                $aligne['historiqueconnu']['aeroportArrivee'] = $dernierVolDeHistoriqueAEtatConnu->getLigne()->getAeroportArrivee()->getCodeIATA();
//                $aligne['historiqueconnu']['aeroportDepart'] = $dernierVolDeHistoriqueAEtatConnu->getLigne()->getAeroportDepart()->getCodeIATA();
//            }
//
//            // ..... IMPORTANT .....
//            // Si il y aune modification de ligne
//            // son action NEW est prioritaire sur une éventuelle action CR provenant d'une modification de période de vol
//            // la modification de jours n'est pas conerné car dans ce if c'est toujours un NEW
//            // si il y a les deux N (modification de ligne) et CR (modification période) l'action à effectuer sera un N
//
//
//            //---------------------------------------------------
//            // Recherche des modifications des jours d'opérations
//            //---------------------------------------------------
//            // si il y a une modification de jour (ajout)
//            // => ACTION ADM (ou faisable avec RPL)
//
//            //day(s) of operation
//            //if( $vol->getPeriodeDeVol()->joursDeValiditeArrayToSCRMessageString($vol->getPeriodeDeVol()->getJoursDeValidite()) != $dernierVolDeHistoriqueAEtatConnu->getPeriodeDeVol()->joursDeValiditeArrayToSCRMessageString($vol->getPeriodeDeVol()->getJoursDeValidite()) ){
//            if( $vol->getPeriodeDeVol()->joursDeValiditeArrayToSCRMessageString() != $dernierVolDeHistoriqueAEtatConnu->getPeriodeDeVol()->joursDeValiditeArrayToSCRMessageString() ){
//                $ajoursdevalidite = $vol->getPeriodeDeVol()->joursDeValiditeArrayToSCRMessageString($vol->getPeriodeDeVol()->getJoursDeValidite());
//            }
//
//
//            //-------------------------------------------------
//            // Recherche des modifications de la période de vol
//            //-------------------------------------------------
//
//            $datetimeFormat = 'Y-m-d H:i:s';
//
//            //périodes du vol fournis: C_vdeb, D_vfin
//            $C_vdeb = date_create_from_format($datetimeFormat,$vol->getPeriodeDeVol()->getDateDebut()->format($datetimeFormat));
//            $C_vdeb->setTime(0,0,0);
//            $D_vfin = date_create_from_format($datetimeFormat,$vol->getPeriodeDeVol()->getDateFin()->format($datetimeFormat));
//            $D_vfin->setTime(0,0,0);
//
//            //période du dernier vol histyorique connu: A_hdeb et B_hfin
//            $A_hdeb = date_create_from_format($datetimeFormat,$dernierVolDeHistoriqueAEtatConnu->getPeriodeDeVol()->getDateDebut()->format($datetimeFormat));
//            $A_hdeb->setTime(0,0,0);
//            $B_hfin = date_create_from_format($datetimeFormat,$dernierVolDeHistoriqueAEtatConnu->getPeriodeDeVol()->getDateFin()->format($datetimeFormat));
//            $B_hfin->setTime(0,0,0);
//
//            $actionDeduitePeriodes='';
//
//
//            $interval1 = $A_hdeb->diff($C_vdeb);
//            $interval2 = $C_vdeb->diff($B_hfin);
//            $interval3 = $D_vfin->diff($B_hfin);
//
//            if( ( intval($interval1->format('%R%a')) == 0  ) && ( intval($interval3->format('%R%a')) == 0 ) ){
//                // Recherche du cas:   AC ... DB
//                // il n'y a pas de changement de période, elles sont identiques
//
//                //ACTION Aucunes actions pour la période
//                $actionDeduitePeriodes = '';
//
//            }else if( ((intval($interval1->format('%R%a'))!= 0) || (intval($interval3->format('%R%a'))!= 0)) && ((intval($interval1->format('%R%a'))>=0) && (intval($interval2->format('%R%a'))>0) && (intval($interval3->format('%R%a'))>=0)) ){
//                // Recherche du cas:   A ... C ... D ... B
//                // la période du vol fournis est comprise dans vol historique
//
//                $datetimeFormat = 'dM';
//                $aperiodes['startperiod'] = $vol->getPeriodeDeVol()->getDateDebut()->format($datetimeFormat);
//                $aperiodes['endperiod'] = $vol->getPeriodeDeVol()->getDateFin()->format($datetimeFormat);
//
//                //ACTION RPL Replacement of Existing Flight Information (C -> D)
//                $actionDeduitePeriodes = 'RPL';
//
//            }else{
//                // Autre cas la période C,D du vol fournis n'est pas comprise dans vol historique et ses bornes ne sont ni égale à A ou à B
//
//                $datetimeFormat = 'dM';
//                $aperiodes['startperiod'] = $vol->getPeriodeDeVol()->getDateDebut()->format($datetimeFormat);
//                $aperiodes['endperiod'] = $vol->getPeriodeDeVol()->getDateFin()->format($datetimeFormat);
//
//                // ANCTION NEW (Les action CNL étant traité à part,
//                // les autres cas de périodes ne concernent que des NEW)
//                $actionDeduitePeriodes = 'NEW';
//            }
//
//            //================================
//            //=        ANALYSE FINALE        =
//            //================================
//            $genererunmessage = true;
//
//            if (count($aligne) != 0) { //Changement de ligne
//                //l'action est obligatoirement un NEW
//                $datamessagetogenerate[$typeDeMessageASMSSM]['action'] = 'NEW';
//
//            }else if(count($aperiodes) != 0){ //Changement de période
//
//                if($actionDeduitePeriodes == 'RPL') {
//
//                    if (count($ajoursdevalidite) != 0) { //Changement de jours de validités
//
//                        if(count($achangements) != 0){ //Il existe d'autres changement
//
//                            //si il y a plus d'un type de changement l'action change
//                            //ACTION RPL Replacement of Existing Flight Information
//                            $datamessagetogenerate[$typeDeMessageASMSSM]['action'] = 'RPL';
//
//                        }else{ //Il n'y a pas d'autre changement que les jours de validités
//
//                            //Si n'il y a qu'un changement de jour de validité {_,_,3,_,_,6,7} (obligatoirement un ajout)
//                            //ACTION ADM Change of Existing Information Expressed by the User of Data Element Identifier
//                            $datamessagetogenerate[$typeDeMessageASMSSM]['action'] = 'ADM';
//
//                        }
//
//                    } else {
//                        //Sinon c'est une modification
//                        //ACTION RPL Replacement of Existing Flight Information
//                        $datamessagetogenerate[$typeDeMessageASMSSM]['action'] = 'RPL';
//                    }
//
//                }else{
//                    //ACTION NEW
//                    $datamessagetogenerate[$typeDeMessageASMSSM]['action'] = 'NEW';
//                }
//
//            }else if(count($achangements) != 0){ //Changement des autres informations
//
//                if(count($aactionDeduiteChangements)==1){//Un seul changement
//                    //ACTION EQT,TIM ou ADM
//                    $datamessagetogenerate[$typeDeMessageASMSSM]['action'] = $aactionDeduiteChangements[0];
//                }else{//Plusieurs changements
//                    //ACTION RPL Replacement of Existing Flight Information
//                    $datamessagetogenerate[$typeDeMessageASMSSM]['action'] = 'RPL';
//                }
//            }else{
//                //Aucune ACTION à effectuer
//                unset($datamessagetogenerate);
//                $datamessagetogenerate = array();
//                $genererunmessage = false;
//            }
//
//
//
//            if($genererunmessage != false) {
//                //=============================================
//                // Les vols à transmettre à la méthode générer
//                //=============================================
//                $datamessagetogenerate['volatraiter'] = $vol;
//                $datamessagetogenerate['voldernierconnu'] = $dernierVolDeHistoriqueAEtatConnu;
//            }
//
//
//        }
//
//
//        // on retourne un array comportant des array de message à générer pour ce vol
//        if( count($datamessagetogenerate) == 0){ //il n'y a aucun message à générer on retourne false;
//            return false;
//        }else{ //on renvois un tableau pour la génération des messages SCR
//            return $datamessagetogenerate;
//        }
//
//
//    }




    /**
     * @inheritdoc
     */
    public function generer(array $datamessagetogenerate){

        $vol = $datamessagetogenerate['volatraiter'];
        if(isset($datamessagetogenerate['voldernierconnu'])) {
            $voldernierconnu = $datamessagetogenerate['voldernierconnu'];
        }else{
            $voldernierconnu = null;
        }
        $aomessages = array();


        //Création d'un objet header
        $oheader = new ASMSSMHeader();
        $oheader->setEchelletemps("UTC");

        //création d'un objet footer
        $ofooter = new ASMSSMFooter();
        /** @var \AirCorsica\XKPlanBundle\Entity\Vol $vol */
        $ofooter->setAeroportarriveecodeIATA($vol->getLigne()->getAeroportArrivee()->getCodeIATA());
        $ofooter->setTerminalaeroportarrivee($vol->getLigne()->getAeroportArrivee()->getTerminal());
        $ofooter->setAeroportdepartcodeIATA($vol->getLigne()->getAeroportDepart()->getCodeIATA());
        $ofooter->setTerminalaeroportdepart($vol->getLigne()->getAeroportDepart()->getTerminal());
        $ofooter->setTypedevol($vol->getTypeDeVol()->getCodeType());

        //============================================================
        // Est-ce qu'on est dans le cas de ASM (multi-message) ou SSM
        //============================================================

        // Si la péiode d'annulation est supérieur à 5 jours (génération d'un SSM)
        // ou inférieur à 5 jours (génération d'un ASM)
        $dateDebutPeriodeVol = $vol->getPeriodeDeVol()->getDateDebut()/*->format('Y-m-d')*/;
        $dateFinPeriodeVol = $vol->getPeriodeDeVol()->getDateFin()/*->format('Y-m-d')*/;
        $interval = $dateDebutPeriodeVol->diff($dateFinPeriodeVol);

        if( intval($interval->format('%R%a')) > 4 ){ //SSM

            //modification du header
            $typeDeMessageASMSSM = "SSM";
            $oheader->setMessagetype($typeDeMessageASMSSM);
            $oheader->setCodeaction($datamessagetogenerate[$typeDeMessageASMSSM]['action']);

            //Création d'un objet action
            $oaction = new ASMSSMAction($typeDeMessageASMSSM,$datamessagetogenerate[$typeDeMessageASMSSM]['action'],$vol->getCodesShareVol());
            $oaction->setAeroportdepartcodeIATA($vol->getLigne()->getAeroportDepart()->getCodeIATA());
            $oaction->setAeroportarriveecodeIATA($vol->getLigne()->getAeroportArrivee()->getCodeIATA());
            $oaction->setCapaciteavion(sprintf("%03d",$vol->getAvion()->getTypeAvion()->getCapaciteSiege()));
            $oaction->setCodeservicetypedevol($vol->getTypeDeVol()->getCodeService());
            $oaction->setCompagniecodeIATA($vol->getAvion()->getCompagnie()->getCodeIATA());
            $oaction->setNumerovol($vol->getNumero());
            $oaction->setTypeavioncodeIATA($vol->getAvion()->getTypeAvion()->getCodeIATA());
            $oaction->setVolheuredepart($vol->getPeriodeDeVol()->getDecollage()->format('Hi'));
            $oaction->setVolheurearrivee($vol->getPeriodeDeVol()->getAtterissage()->format('Hi'));
            //data spécifique au message SSM
            $ddeb = $vol->getPeriodeDeVol()->getDateDebut();
            $dfin = $vol->getPeriodeDeVol()->getDateFin();
            $oaction->setDatedebutperiodedevol($ddeb->format('dMy'));
            $oaction->setDatefinperiodedevol($dfin->format('dMy'));
            $oaction->setJoursvaliditeduvol($vol->getPeriodeDeVol()->joursDeValiditeArrayToSSMMessageString());

            //on crée un objet message
            $omessage = new ASMSSMMessage();
            $omessage->setIdVol($vol->getId());
            $omessage->setLibelle('SSM:'.$datamessagetogenerate[$typeDeMessageASMSSM]['action']);
            $omessage->setHeader($oheader);
            $omessage->setAction($oaction);
            $omessage->setFooter($ofooter);

            // Recherche des DESTINATAIRES
            // Adresse SITA des destinataires appartenant aux groupes suivants:
            // ASM SSM BASE + ASM SSM Aeroport de départ + ASM SSM Aeroprt d'arrivée + MUCSCXK groupe par defaut ALTEA
            $criteria = array('aeroportAttache'=> $vol->getLigne()->getAeroportDepart());
            $aAdressesSITADepart = $this->em->getRepository('AirCorsicaXKPlanBundle:AdresseSITA')->findBy($criteria);

            $criteria = array('aeroportAttache'=> $vol->getLigne()->getAeroportArrivee());
            $aAdressesSITAArrivee = $this->em->getRepository('AirCorsicaXKPlanBundle:AdresseSITA')->findBy($criteria);

            $criteria = array('nom'=> 'ASM SSM BASE');
            $groupeSITABase = $this->em->getRepository('AirCorsicaXKPlanBundle:GroupeSITA')->findBy($criteria);
            $criteria = array('groupeSITA'=> $groupeSITABase);
            $aAdressesSITABase = $this->em->getRepository('AirCorsicaXKPlanBundle:AdresseSITA')->findBy($criteria);

            //Est-ce que l'avion utilisé pour ce vol appartient à une autre compagnie (avion afreté)
            if( $vol->getAvion()->getAffrete() ){//avion afreté

                //on recherche la compagnie de cet avion
                $compagnieProprietaireDeAvion = $vol->getAvion()->getCompagnie();

                //on recherche les adresse SITA de cette compagnie sans aeroport d'attache
                $criteria = array('aeroportAttache'=> null,'compagnieAttachee'=>$compagnieProprietaireDeAvion);
                $aAdressesSITACompagnieAvionAffrete = $this->em->getRepository('AirCorsicaXKPlanBundle:AdresseSITA')->findBy($criteria);

                //on rassemble les adresses SITA
                $aAdressesSITA = array_merge($aAdressesSITADepart,$aAdressesSITAArrivee,$aAdressesSITABase,$aAdressesSITACompagnieAvionAffrete);
            }else{
                //on rassemble les adresses SITA
                $aAdressesSITA = array_merge($aAdressesSITADepart,$aAdressesSITAArrivee,$aAdressesSITABase);
            }




            // Enregistrement des DESTINATAIRES
            //on les enregistre dans les destinataires du message
            foreach ($aAdressesSITA as $uneAdresseSITA) {
                //$omessage->addDestinataires($uneAdresseSITA->getAdresseSITA()); //appel de la méthode de la classe abstraire AbstractMessage
                $omessage->addDestinataires(array('email' => $uneAdresseSITA->getEmail(), 'adresse' => $uneAdresseSITA->getAdresseSITA(), 'groupe' => $uneAdresseSITA->getGroupeSITA()->getNom(), 'libelle' => $uneAdresseSITA->getLibelle(), 'coordinateur' => $uneAdresseSITA->getPaysCoordinateur()));
            }

            //Si il s'agit d'un vol XK régulier REG ou supplémentaire SUP
            //on rajoute l'adresse de messagerie ALTEA
            if( (strpos(strtoupper ($vol->getNumero()),"XK") !== false ) && (($vol->getTypeDeVol()->getCodeType() == "REG") || ($vol->getTypeDeVol()->getCodeType() == "SUP")) ){

                //on rajoute l'adresse d'ALTEA qui est définie dans les préférences de l'application (libellé: ALTEA, groupe: groupe par defaut) cf XKPlan Xavier
                //$omessage->addDestinataires('MUCSCXK');
                $omessage->addDestinataires(array('email' => $uneAdresseSITA->getEmail(),'adresse' => 'MUCSCXK', 'groupe' => 'DEFAUT', 'libelle' => 'ALTEA', 'coordinateur' => ''));

            }

            //rajout du message SSM à l'array message $amessages
            array_push($aomessages,$omessage);

        }else{ //ASM

            //modification du header
            $typeDeMessageASMSSM = "ASM";
            $oheader->setMessagetype($typeDeMessageASMSSM);
            $oheader->setCodeaction($datamessagetogenerate[$typeDeMessageASMSSM]['action']);


            // Recherche des DESTINATAIRES
            // Adresse SITA des destinataires appartenant aux groupes suivants:
            // ASM SSM BASE + ASM SSM Aeroport de départ + ASM SSM Aeroprt d'arrivée + MUCSCXK groupe par defaut ALTEA
            $criteria = array('aeroportAttache'=> $vol->getLigne()->getAeroportDepart());
            $aAdressesSITADepart = $this->em->getRepository('AirCorsicaXKPlanBundle:AdresseSITA')->findBy($criteria);

            $criteria = array('aeroportAttache'=> $vol->getLigne()->getAeroportArrivee());
            $aAdressesSITAArrivee = $this->em->getRepository('AirCorsicaXKPlanBundle:AdresseSITA')->findBy($criteria);

            $criteria = array('nom'=> 'ASM SSM BASE');
            $groupeSITABase = $this->em->getRepository('AirCorsicaXKPlanBundle:GroupeSITA')->findBy($criteria);
            $criteria = array('groupeSITA'=> $groupeSITABase);
            $aAdressesSITABase = $this->em->getRepository('AirCorsicaXKPlanBundle:AdresseSITA')->findBy($criteria);


            //Est-ce que l'avion utilisé pour ce vol appartient à une autre compagnie (avion afreté)
            if( $vol->getAvion()->getAffrete() ){//avion afreté

                //on recherche la compagnie de cet avion
                $compagnieProprietaireDeAvion = $vol->getAvion()->getCompagnie();

                //on recherche les adresse SITA de cette compagnie sans aeroport d'attache
                $criteria = array('aeroportAttache'=> null,'compagnieAttachee'=>$compagnieProprietaireDeAvion);
                $aAdressesSITACompagnieAvionAffrete = $this->em->getRepository('AirCorsicaXKPlanBundle:AdresseSITA')->findBy($criteria);

                //on rassemble les adresses SITA
                $aAdressesSITA = array_merge($aAdressesSITADepart,$aAdressesSITAArrivee,$aAdressesSITABase,$aAdressesSITACompagnieAvionAffrete);
            }else{
                //on rassemble les adresses SITA
                $aAdressesSITA = array_merge($aAdressesSITADepart,$aAdressesSITAArrivee,$aAdressesSITABase);
            }


            for ($i=0;$i <= intval($interval->format('%R%a')); $i++ ){

                //on calcul la date relative à l'action du message ASM
                $dateDebut = clone $vol->getPeriodeDeVol()->getDateDebut();
                $dateDebut->modify('+'.$i.' day');

                //Création d'un objet action
                $oaction = new ASMSSMAction($typeDeMessageASMSSM,$datamessagetogenerate[$typeDeMessageASMSSM]['action'],$vol->getCodesShareVol());
                $oaction->setAeroportdepartcodeIATA($vol->getLigne()->getAeroportDepart()->getCodeIATA());
                $oaction->setAeroportarriveecodeIATA($vol->getLigne()->getAeroportArrivee()->getCodeIATA());
                $oaction->setCapaciteavion(sprintf("%03d",$vol->getAvion()->getTypeAvion()->getCapaciteSiege()));
                $oaction->setCodeservicetypedevol($vol->getTypeDeVol()->getCodeService());
                $oaction->setCompagniecodeIATA($vol->getCompagnie()->getCodeIATA());
                $oaction->setNumerovol($vol->getNumero());
                $oaction->setTypeavioncodeIATA($vol->getAvion()->getTypeAvion()->getCodeIATA());
                $oaction->setVolheuredepart($vol->getPeriodeDeVol()->getDecollage()->format('Hi'));
                $oaction->setVolheurearrivee($vol->getPeriodeDeVol()->getAtterissage()->format('Hi'));
                //data spécifique au message ASM
                $oaction->setDateduvol($dateDebut->format('dMy'));

                //vérification que la date de ce message ASM fait bien partie des jours de validités
                $ajourDeValiditeATester = array_diff($vol->getPeriodeDeVol()->getJoursDeValidite(), array('-')); //on enléve les - de l'array, il ne reste que les id des jours valide
                if(in_array(strval(date('N', $dateDebut->getTimestamp())),$ajourDeValiditeATester)) {
                    //ce jour fait partie des jour de validité, ont crée le message ASM

                    //on crée un objet message
                    $omessage = new ASMSSMMessage();
                    $omessage->setIdVol($vol->getId());
                    $omessage->setLibelle('ASM:' . $datamessagetogenerate[$typeDeMessageASMSSM]['action']);
                    $omessage->setHeader($oheader);
                    $omessage->setAction($oaction);
                    $omessage->setFooter($ofooter);

                    // Enregistrement des DESTINATAIRES
                    //on les enregistre dans les destinataires du message
                    foreach ($aAdressesSITA as $uneAdresseSITA) {
                        //$omessage->addDestinataires($uneAdresseSITA->getAdresseSITA()); //appel de la méthode de la classe abstraire AbstractMessage
                        $omessage->addDestinataires(array('email' => $uneAdresseSITA->getEmail(), 'adresse' => $uneAdresseSITA->getAdresseSITA(), 'groupe' => $uneAdresseSITA->getGroupeSITA()->getNom(), 'libelle' => $uneAdresseSITA->getLibelle(), 'coordinateur' => $uneAdresseSITA->getPaysCoordinateur()));
                    }

                    //Si il s'agit d'un vol XK régulier REG ou supplémentaire SUP
                    //on rajoute l'adresse de messagerie ALTEA
                    if ((strpos(strtoupper($vol->getNumero()), "XK") !== false) && (($vol->getTypeDeVol()->getCodeType() == "REG") || ($vol->getTypeDeVol()->getCodeType() == "SUP"))) {

                        //on rajoute l'adresse d'ALTEA qui est définie dans les préférences de l'application (libellé: ALTEA, groupe: groupe par defaut) cf XKPlan Xavier
                        //$omessage->addDestinataires('MUCSCXK');
                        $omessage->addDestinataires(array('email' => $this->container->getParameter('emailsitaaltea')/*''*/, 'adresse' => $this->container->getParameter('adressesitaaltea')/*'MUCSCXK'*/, 'groupe' => 'DEFAUT', 'libelle' => 'ALTEA', 'coordinateur' => ''));

                    }

                    //rajout d'un message ASM à l'array message $amessages
                    array_push($aomessages, $omessage);

                }

            }
        }

        //on renvoi le ou les messages créées
        return $aomessages;
    }



}