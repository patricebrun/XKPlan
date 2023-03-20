<?php

namespace AirCorsica\XKPlanBundle\Entity\Message;


use AirCorsica\XKPlanBundle\Entity\Message\SCRMessage;
use AirCorsica\XKPlanBundle\Entity\Message\SCRHeader;
use AirCorsica\XKPlanBundle\Entity\Message\SCRAction;
use AirCorsica\XKPlanBundle\Entity\Message\SCRFooter;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * SCR
 */
class SCR implements InterfaceMessage
{

    /**
     * tableau associatif des aeroports coordonnateurs du vol
     *
     * @var array
     */
    private $aaeroportscoordonateurs;

    private $container;

    private $em;

    /**
     * SCR constructor.
     * @param \Doctrine\ORM\EntityManager $em
     * @param $container
     */
    function __construct($vol,$em,$container)
    {


        //l'entity manager
        $this->em   = $em;

        //le container
        $this->container  = $container;

        // le(les) aeroport(s) coodonnateur(s) de départ ou(et) d'arrivée du vol
        $aaeroportscoordonateurs = array();

        for($i=1;$i<=2;$i++){

            switch ($i) {
                case 1: //aeroport de départ
                    $criteria = array('codeIATA'=>$vol->getLigne()->getAeroportDepart()->getCodeIATA(),'coordonne'=>true);
                    $key = "depart";
                    break;
                case 2: //aeroport d'arrivée
                    $criteria = array('codeIATA'=>$vol->getLigne()->getAeroportArrivee()->getCodeIATA(),'coordonne'=>true);
                    $key = "arrivee";
                    break;
            }

            if($em->getRepository('AirCorsicaXKPlanBundle:Aeroport')->findBy($criteria) != null){
                $aeroportTrouve = $em->getRepository('AirCorsicaXKPlanBundle:Aeroport')->findBy($criteria);
                //$aaeroportscoordonateurs['vol'][$key] = $aeroportTrouve[0];
                $aaeroportscoordonateurs[$key] = $aeroportTrouve[0];
            }

        }

        $this->aaeroportscoordonateurs = $aaeroportscoordonateurs;

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
//        // -> soit la periode vol est en état pendingcancel et c'est un message SCR de Delestage qu'il faut envoyer
//        // -> soit la periode vol est en pendingsend et les différences sont à analyser pour en déduire les message SCR à envoyer
//
//        $datamessagetogenerate = array();
//        $achangements = array();
//        $aligne = array();
//        $ajoursdevalidite = array();
//        $aperiodes = array();
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
//        // Est-ce que le vol transmis est en pendingCancel?
//        if( $vol->getPeriodeDeVol()->getEtat() == 'pendingCancel'){
//
//            // le pendingCancel concerne des vol à supprimer (supression de type periode/jour/ligne...
//            $datamessagetogenerate['SCR']['action'] = 'D';
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
//                //----------------------------------------------------------------------
//                // Recherche des modifications autres que la période de vol et la ligne
//                //----------------------------------------------------------------------
//                // si il y a des changements
//                // => ACTION CR CANCEL/REPLACE
//
//
//                // Code Share (Ne génére pas de SCR)
//                // Nature de vol (Ne génére pas de SCR)
//                // Compagnie (Ne génére pas de SCR)
//                // Affretement (Ne génére pas de SCR)
//                // Commentaire (Ne génére pas de SCR)
//                // On ne peut pas changer le numero du vol
//
//                // Numéro
////                if( $vol->getNumero() != $dernierVolDeHistoriqueAEtatConnu->getNumero() ){
////                    $achangements['numero'] = $vol->getNumero();
////                }
//
//                // Type de vol
//                if( $vol->getTypeDeVol()->getCodeService() != $dernierVolDeHistoriqueAEtatConnu->getTypeDeVol()->getCodeService() ){
//                    $achangements['service'] = $vol->getTypeDeVol()->getCodeService();
//                }
//
//                // Type d'Avion ( on récupére le nombre de siéges -3chiffres- suivit du IATA aircraft subtype - 3 alphanumeric - ))
//                if( $vol->getAvion()->getTypeAvion()->getCodeIATA() != $dernierVolDeHistoriqueAEtatConnu->getAvion()->getTypeAvion()->getCodeIATA() ){
//                    $capaciteMsg = sprintf("%'.03d\n", $vol->getAvion()->getTypeAvion()->getCapaciteSiege()); //on format le retour pour toujour avoir 3 chiffes cad 30 -> 030 et 2 -> 002
//                    $achangements['avion'] = $vol->getAvion()->getTypeAvion()->getCodeIATA().$capaciteMsg;
//                }
//
//                //required arrival time
//                $datetimeFormat = 'Hi';
//                if( $vol->getPeriodeDeVol()->getAtterissage()->format($datetimeFormat) != $dernierVolDeHistoriqueAEtatConnu->getPeriodeDeVol()->getAtterissage()->format($datetimeFormat) ){
//                    $achangements['arrival'] = $vol->getPeriodeDeVol()->getAtterissage()->format($datetimeFormat);
//                }
//
//                //require departure time
//                $datetimeFormat = 'Hi';
//                if( $vol->getPeriodeDeVol()->getDecollage()->format($datetimeFormat) != $dernierVolDeHistoriqueAEtatConnu->getPeriodeDeVol()->getDecollage()->format($datetimeFormat) ){
//                    $achangements['departure'] = $vol->getPeriodeDeVol()->getDecollage()->format($datetimeFormat);
//                }
//
//
//                //-----------------------------------------
//                // Recherche des modifications de la ligne
//                //-----------------------------------------
//                // si il y a une modification de ligne
//                // => ACTION NEW
//
//                if( ($vol->getLigne()->getAeroportArrivee()->getCodeIATA() != $dernierVolDeHistoriqueAEtatConnu->getLigne()->getAeroportArrivee()->getCodeIATA()) || ($vol->getLigne()->getAeroportDepart()->getCodeIATA() != $dernierVolDeHistoriqueAEtatConnu->getLigne()->getAeroportDepart()->getCodeIATA()) ){
//                    $aligne['Vol']['aeroportArrivee'] = $vol->getLigne()->getAeroportArrivee()->getCodeIATA();
//                    $aligne['Vol']['aeroportDepart'] = $vol->getLigne()->getAeroportDepart()->getCodeIATA();
//                    $aligne['historiqueconnu']['aeroportArrivee'] = $dernierVolDeHistoriqueAEtatConnu->getLigne()->getAeroportArrivee()->getCodeIATA();
//                    $aligne['historiqueconnu']['aeroportDepart'] = $dernierVolDeHistoriqueAEtatConnu->getLigne()->getAeroportDepart()->getCodeIATA();
//                }
//
//                // ..... IMPORTANT .....
//                // Si il y aune modification de ligne
//                // son action NEW est prioritaire sur une éventuelle action CR provenant d'une modification de période de vol
//                // la modification de jours n'est pas conerné car dans ce if c'est toujours un NEW
//                // si il y a les deux N (modification de ligne) et CR (modification période) l'action à effectuer sera un N
//
//
//                //---------------------------------------------------
//                // Recherche des modifications des jours d'opérations
//                //---------------------------------------------------
//                // si il y a une modification de jour
//                // => ACTION NEW
//
//                //day(s) of operation
//                //if( $vol->getPeriodeDeVol()->joursDeValiditeArrayToSCRMessageString($vol->getPeriodeDeVol()->getJoursDeValidite()) != $dernierVolDeHistoriqueAEtatConnu->getPeriodeDeVol()->joursDeValiditeArrayToSCRMessageString($vol->getPeriodeDeVol()->getJoursDeValidite()) ){
//                if( $vol->getPeriodeDeVol()->joursDeValiditeArrayToSCRMessageString() != $dernierVolDeHistoriqueAEtatConnu->getPeriodeDeVol()->joursDeValiditeArrayToSCRMessageString() ){
//                    $ajoursdevalidite = $vol->getPeriodeDeVol()->joursDeValiditeArrayToSCRMessageString($vol->getPeriodeDeVol()->getJoursDeValidite());
//                }
//
//                // ..... IMPORTANT .....
//                // Si il y aune modification de jour
//                // son action NEW est prioritaire sur une éventuelle action CR provenant d'une modification de période de vol
//                // si il y a les deux N (modification de jour) et CR (modification période) l'action à effectuer sera un N
//
//
//                //-------------------------------------------------
//                // Recherche des modifications de la période de vol
//                //-------------------------------------------------
//
//                //start of period or single day
//                $interval = $vol->getPeriodeDeVol()-> getDateDebut()->diff($dernierVolDeHistoriqueAEtatConnu->getPeriodeDeVol()->getDateDebut());
//                if( intval($interval->format('%R%a')) !=0 ){// si les 2 datetime ne sont pas indentique
//                    $datetimeFormat = 'dM';
//                    $aperiodes['startperiod'] = $vol->getPeriodeDeVol()->getDateDebut()->format($datetimeFormat);
//                }
//
//                //end of period or single day
//                $interval = $vol->getPeriodeDeVol()->getDateFin()->diff($dernierVolDeHistoriqueAEtatConnu->getPeriodeDeVol()->getDateFin());
//                if( intval($interval->format('%R%a')) !=0 ){// si les 2 datetime ne sont pas indentique
//                    $datetimeFormat = 'dM';
//                    $aperiodes['endperiod'] = $vol->getPeriodeDeVol()->getDateFin()->format($datetimeFormat);
//                }
//
//                    //=====================================================================================================
//                    // Analyse des périodes du vol C_vdeb, D_vfin et du dernier vol histyorique connu A_hdeb et B_hfin
//                    // avec obligatoirement C <= D et A <= B
//                    // les actions multiples sont créé lorsqu'il y a des modifications de période.
//                    //=====================================================================================================
//                    $datetimeFormat = 'Y-m-d H:i:s';
//
//                    $C_vdeb = date_create_from_format($datetimeFormat,$vol->getPeriodeDeVol()->getDateDebut()->format($datetimeFormat));
//                    $C_vdeb->setTime(0,0,0);
//
//                    $D_vfin = date_create_from_format($datetimeFormat,$vol->getPeriodeDeVol()->getDateFin()->format($datetimeFormat));
//                    $D_vfin->setTime(0,0,0);
//
//                    $A_hdeb = date_create_from_format($datetimeFormat,$dernierVolDeHistoriqueAEtatConnu->getPeriodeDeVol()->getDateDebut()->format($datetimeFormat));
//                    $A_hdeb->setTime(0,0,0);
//
//                    $B_hfin = date_create_from_format($datetimeFormat,$dernierVolDeHistoriqueAEtatConnu->getPeriodeDeVol()->getDateFin()->format($datetimeFormat));
//                    $B_hfin->setTime(0,0,0);
//
//                    $actionDeduitePeriodes='';
//
//
//                    // --------------
//                    // Cas: AC -> BD
//                    // --------------
//                    $interval1 = $C_vdeb->diff($A_hdeb);
//                    $interval2 = $D_vfin->diff($B_hfin);
//                    if( ( intval($interval1->format('%R%a')) == 0 ) && ( intval($interval2->format('%R%a')) == 0 ) ){
//                        // *************************************************************
//                        //ACTION CR CANCEL/REPLACE (C -> B)
//                        // si il y a d'autres changements entre vol et historique connu
//                        $actionDeduitePeriodes = 'CR_dbg1:'.$actionDeduitePeriodes;
//                        // *************************************************************
//                    }
//
//
//                    // -----------------------
//                    // Cas: C -> D ... A -> B
//                    // -----------------------
//                    $interval1 = $D_vfin->diff($A_hdeb);
//                    if( intval($interval1->format('%R%a')) > 0 ){
//                        // *******************
//                        //ACTION NEW (C -> D)
//                        $actionDeduitePeriodes = 'N_dbg2:'.$actionDeduitePeriodes;
//                        // *******************
//                    }
//
//
//                    // -----------------------
//                    // Cas: A -> B ... C -> D
//                    // -----------------------
//                    $interval1 = $B_hfin->diff($C_vdeb);
//                    if( intval($interval1->format('%R%a')) > 0 ){
//                        // *******************
//                        //ACTION NEW (C -> D)
//                        $actionDeduitePeriodes = 'N_dbg3:'.$actionDeduitePeriodes;
//                        // *******************
//                    }
//
//
//                    // ------------------
//                    // Cas: C -> DA -> B
//                    // ------------------
//                    $interval1 = $D_vfin->diff($A_hdeb);
//                    if( intval($interval1->format('%R%a')) == 0 ){
//                        // *******************
//                        //ACTION NEW (C -> D)
//                        $actionDeduitePeriodes = 'N_dbg4:'.$actionDeduitePeriodes;
//                        // *******************
//                    }
//
//
//                    // ------------------
//                    // Cas: A -> BC -> D
//                    // ------------------
//                    $interval1 = $B_hfin->diff($C_vdeb);
//                    if( intval($interval1->format('%R%a')) == 0 ){
//                        // *******************
//                        //ACTION NEW (C -> D)
//                        $actionDeduitePeriodes = 'N_dbg5:'.$actionDeduitePeriodes;
//                        // *******************
//                    }
//
//
//                    // ----------------------
//                    // Cas: A -> C -> B -> D
//                    // ----------------------
//                    $interval1 = $A_hdeb->diff($C_vdeb);
//                    $interval2 = $B_hfin->diff($C_vdeb);
//                    $interval3 = $B_hfin->diff($D_vfin);
//
//                    if( ( intval($interval1->format('%R%a')) > 0 ) && ( intval($interval2->format('%R%a')) < 0 ) && ( intval($interval3->format('%R%a')) == 0 ) ){
//                        // *************************************************************
//                        //ACTION CR CANCEL/REPLACE (C -> B)
//                        // si il y a d'autres changements entre vol et historique connu
//                        $actionDeduitePeriodes = 'CR_dbg6:'.$actionDeduitePeriodes;
//                        // *************************************************************
//                    }
//
//                    if( ( intval($interval1->format('%R%a')) > 0 ) && ( intval($interval2->format('%R%a')) > 0 ) && ( intval($interval3->format('%R%a')) > 0 ) ){
//                        // *******************
//                        //ACTION NEW (B -> D)
//                        $actionDeduitePeriodes = 'N_dbg7:'.$actionDeduitePeriodes;
//                        // *******************
//                    }
//
//                    // ----------------------
//                    // Cas: C -> A -> D -> B
//                    // ----------------------
//                    $interval1 = $C_vdeb->diff($A_hdeb);
//                    $interval2 = $D_vfin->diff($A_hdeb);
//                    $interval3 = $D_vfin->diff($B_hfin);
//
//                    if( ( intval($interval1->format('%R%a')) > 0 ) && ( intval($interval2->format('%R%a')) > 0 ) && ( intval($interval3->format('%R%a')) > 0 ) ){
//                        // *******************
//                        //ACTION NEW (C -> A)
//                        $actionDeduitePeriodes = 'N_dbg8:'.$actionDeduitePeriodes;
//                        // *******************
//                    }
//
//                    if( ( intval($interval1->format('%R%a')) == 0 ) && ( intval($interval2->format('%R%a')) > 0 ) && ( intval($interval3->format('%R%a')) > 0 ) ){
//                        // *************************************************************
//                        //ACTION CR CANCEL/REPLACE (A -> D)
//                        // si il y a d'autres changements entre vol et historique connu
//                        $actionDeduitePeriodes = 'CR_dbg9:'.$actionDeduitePeriodes;
//                        // *************************************************************
//                    }
//
//                    // -----------------------
//                    //  Cas: C -> A -> B -> D
//                    // -----------------------
//                    $interval1 = $C_vdeb->diff($A_hdeb);
//                    $interval2 = $D_vfin->diff($A_hdeb);
//
//                    if( ( intval($interval1->format('%R%a')) > 0 ) && ( intval($interval2->format('%R%a')) > 0 ) ){
//                        // *******************
//                        //ACTION NEW (C -> A)
//                        $actionDeduitePeriodes = 'N_dbg11:'.$actionDeduitePeriodes;
//                        // *******************
//                    }
//
//                    $interval1 = $C_vdeb->diff($A_hdeb);
//                    $interval2 = $D_vfin->diff($B_hfin);
//                    if( ( intval($interval1->format('%R%a')) == 0 ) && ( intval($interval2->format('%R%a')) == 0 ) ){
//                        // *************************************************************
//                        //ACTION CR CANCEL/REPLACE (A -> B)
//                        // si il y a d'autres changements entre vol et historique connu
//                        $actionDeduitePeriodes = 'CR_dbg12:'.$actionDeduitePeriodes;
//                        // *************************************************************
//                    }
//
//                    $interval1 = $B_hfin->diff($D_vfin);
//                    $interval2 = $B_hfin->diff($C_vdeb);
//                    if( ( intval($interval1->format('%R%a')) > 0 ) && ( intval($interval2->format('%R%a')) > 0 ) ){
//                        // *******************
//                        //ACTION NEW (B -> D)
//                        $actionDeduitePeriodes = 'N_dbg13:'.$actionDeduitePeriodes;
//                        // *******************
//                    }
//
//
//                    // -----------------------
//                    // Cas: A -> C -> D -> B
//                    // -----------------------
//                    $interval1 = $A_hdeb->diff($C_vdeb);
//                    $interval2 = $C_vdeb->diff($B_hfin);
//                    $interval3 = $D_vfin->diff($B_hfin);
//
//                    if( ( intval($interval1->format('%R%a')) > 0  ) && ( intval($interval2->format('%R%a')) > 0 ) && ( intval($interval3->format('%R%a')) > 0 ) ){
//                        // *************************************************************
//                        //ACTION CR CANCEL/REPLACE (C -> D)
//                        // si il y a d'autres changements entre vol et historique connu
//                        $actionDeduitePeriodes = 'CR_dbg14:'.$actionDeduitePeriodes;
//                        // *************************************************************
//                    }
//
//            //=====================================================================
//            // Déduction de l'action du message à transmettre à la méthode générer
//            //=====================================================================
//            //Le type d'action du message est déterminé par l'ordre de priorité suivante:
//            //1 - changement de ligne N
//            //2 - changement de jours N
//            //3 - changement de période N ou CR
//            //4 - changement d'autres information CR
//
//            $genererunmessage = true;
//
//            // déduction de l'action à effectuer dans le message
//            if (count($aligne) != 0){ // Action NEW
//                $datamessagetogenerate['SCR']['action'] = 'N';
//            }else{
//
//                if (count($ajoursdevalidite) != 0){ // Action NEW
//                    $datamessagetogenerate['SCR']['action'] = 'N';
//                }else{
//
//                    if(count($aperiodes) != 0){ // Action NEW ou Action CANCEL/REPLACE
//                        $debugActionDeduitePeriodes = explode('_',$actionDeduitePeriodes);
//                        if($debugActionDeduitePeriodes[0] == 'CR'){ // Action CR
//
//                            if(count($achangements) != 0){ // Action CANCEL/REPLACE
//                                $datamessagetogenerate['SCR']['action'] = 'CR';
//                            }else{  // Aucun message à génerer les informations sont déja connus
//                                unset($datamessagetogenerate);
//                                $datamessagetogenerate = array();
//                                $genererunmessage = false;
//                            }
//
//                        }else{ // Action New
//                            $datamessagetogenerate['SCR']['action'] = $debugActionDeduitePeriodes[0];
//                        }
//
//                    }else{
//
//                        if(count($achangements) != 0){ // Action CANCEL/REPLACE
//                            $datamessagetogenerate['SCR']['action'] = 'CR';
//                        }else{  // Aucun message à génerer les informations sont déja connus
//                            unset($datamessagetogenerate);
//                            $datamessagetogenerate = array();
//                            $genererunmessage = false;
//                        }
//
//                    }
//
//                }
//            }
//
//            if($genererunmessage != false) {
//                //=============================================
//                // Les vols à transmettre à la méthode générer
//                //=============================================
//                $datamessagetogenerate['volatraiter'] = $vol;
//                $datamessagetogenerate['voldernierconnu'] = $dernierVolDeHistoriqueAEtatConnu;
//            }
//
//        }
//
//
//        // on retourne un array comportant des array de message à générer pour ce vol
//        if( count($datamessagetogenerate) == 0){ //il n'y a aucun message à générer on retourne false;
//                return false;
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

//        //Création d'un objet header
//        $oheader = new SCRHeader();
//        $oheader->setMessagetype("SCR"); //type de message
//        $oheader->setEmailoriginator($this->container->getParameter('messagereplyaddress')); //adresse de réponse en cas de problème
//        $dateDEnvoieDuMessage = date_create();
//        $oheader->setDateofmessage(strtoupper($dateDEnvoieDuMessage->format('dM'))); //date d'envoi du message SCR

        //création d'un objet footer
        $ofooter = new SCRFooter();
        $ofooter->setSupplementaryinformation("");
        $ofooter->setGeneralinformation("");

        // =========================================================================================================================
        // On regarde quel(s) aeroport(s) est coordonateur sur cette ligne ( aeroport de depart et/ou aeroport arrive )
        // chaque aeroport coordonateur sera destinataire d'un message de supression soit d'un vol d'arrivé soit d'un vol de départ
        // =========================================================================================================================

        foreach ($this->aaeroportscoordonateurs as $keytypedaeroport => $unaeroport) {

            //Création d'un objet header
            $oheader = new SCRHeader();
            $oheader->setMessagetype("SCR"); //type de message
            $oheader->setEmailoriginator($this->container->getParameter('messagereplyaddress')); //adresse de réponse en cas de problème
            $dateDEnvoieDuMessage = date_create();
            $oheader->setDateofmessage(strtoupper($dateDEnvoieDuMessage->format('dM'))); //date d'envoi du message SCR
            //le code IATA de l'aeroport coodonnateur concernant le message
            $oheader->setClearanceairportconcerned($unaeroport->getCodeIATA());

            $dateDebutPeriodeVol = $vol->getPeriodeDeVol()->getDateDebut()->format('Y-m-d');
            $dateFinPeriodeVol = $vol->getPeriodeDeVol()->getDateFin()->format('Y-m-d');

            /*
            * La norme des message SCR implique que chaque message ne doit concerner qu'une seule période IATA
            * Si une période de vol chevauche 2 période IATA, il faudrait générer 2 messages un par demi-période
            * La version d'XKPlan de Xavier ne le prends pas en compte, le code suivant commenté implémente cette fonctionnalité
            */

/*
            // ================================================================================
            //       On vérifie que la période de vol est bien dans une unique saison IATA
            // ================================================================================

            //la periode de vol chevauche plusieurs périodes IATA: il y aura autant de messages que de sous période IATA
            if($this->isPeriodIncludedInOneIATASeason($dateDebutPeriodeVol,$dateFinPeriodeVol) == false){

                //on récupére le tableau des sous-périodes à partir des infos de $vol->getPeriodeDeVol()
                $aperiodesDeSupression = $this->getSubIATAPeriodsFromAPeriod($dateDebutPeriodeVol,$dateFinPeriodeVol);

                //pour chaque sous périodes IATA
                foreach ($aperiodesDeSupression as $unesousperiode){

                    //Mise à jour de la période IATA concernant le message
                    $header = str_replace("SaisonIATAConcerne",$unesousperiode['codeSaison'],$header);

                    //appel de la fonction de génération de la dataline
                    $datalines = $this->generationDataLineDeleteRequest($vol,$unesousperiode,$this->container->getParameter('codeaeroportdattacheaircorsica'),$keytypedaeroport);

                    //création du message SCR et rajout à l'array message $amessages
                    array_push($amessages,$header."\r".$datalines."\r".$footer);

                }

            }else{ //la periode de vol est incluse dans une saison IATA: il y a un seul message pour cette période de vol
*/

                //Mise à jour de la période IATA concernant le message
                $oheader->setIATAseasonconcerned($this->getCodeSaisonIATAFromDate($dateDebutPeriodeVol));

                //on crée un objet message
                $omessage = new SCRMessage();
                $omessage->setIdVol($vol->getId());
                    switch ($datamessagetogenerate['SCR']['action']) {
                        case 'D': // delestage d'un vol
                            $libelleaction='DELETE';
                            break;
                        case 'N': // création d'un nouveau vol
                            $libelleaction='NEW';
                            break;
                        case 'CR': // modifications d'un vol existant
                            $libelleaction='CHANGE/REVISION';
                            break;
                    }
                $omessage->setLibelle('SCR:'.$libelleaction);
                //$omessage->setLibelle('SCR:'.$datamessagetogenerate['SCR']['action']);
                $omessage->setHeader($oheader);
                $omessage->setFooter($ofooter);

                // *********************
                //     DESTINATAIRES
                // *********************

                // on récupére les adresses SITA ayant la case "suivit de demande de slot coché" cad normalement qu'AIR CORSICA
                $aAdressesSITASuivitDemandeDeSlot = array();
                $criteria = array('suiviDemandeSlot'=> true);
                $aAdressesSITASuivitDemandeDeSlot = $this->em->getRepository('AirCorsicaXKPlanBundle:AdresseSITA')->findBy($criteria);

                // on récupére les adresse SITA des aeropports coordonnateurs du pays de l'aeroport concerné par le message
                $aAdressesSITACoordination = array();
                $criteria = array('codeIATA'=> $oheader->getClearanceairportconcerned());
                $aeroportConcerne = $this->em->getRepository('AirCorsicaXKPlanBundle:Aeroport')->findBy($criteria);
                $criteria = array('nom'=>'COORDINATIONS');
                $groupeSITACoordination = $this->em->getRepository('AirCorsicaXKPlanBundle:GroupeSITA')->findBy($criteria);
                $criteria = array('paysCoordinateur'=> $aeroportConcerne[0]->getPays(),'groupeSITA'=>$groupeSITACoordination);
                $aAdressesSITACoordination = $this->em->getRepository('AirCorsicaXKPlanBundle:AdresseSITA')->findBy($criteria);

                //on rassemble les adresses SITA
                $aAdressesSITA = array_merge($aAdressesSITASuivitDemandeDeSlot,$aAdressesSITACoordination);

                // on les enregistre dans les destinataires du message
                foreach ($aAdressesSITA as $uneAdresseSITA) {
                    //$omessage->addDestinataires($uneAdresseSITA->getAdresseSITA()); //appel de la méthode de la classe abstraire AbstractMessage
                    $omessage->addDestinataires(array('email' => $uneAdresseSITA->getEmail(),'adresse' => $uneAdresseSITA->getAdresseSITA(), 'groupe' => $uneAdresseSITA->getGroupeSITA()->getNom(), 'libelle' => $uneAdresseSITA->getLibelle(), 'coordinateur' => $uneAdresseSITA->getPaysCoordinateur()));
                }

                $oaction = new SCRAction();
                $aoaction = array();

                //appel de la fonction de génération de la dataline
                switch ($datamessagetogenerate['SCR']['action']) {

                    // delestage d'un vol
                    case 'D':
                        $oaction = $this->createActionDeleteOrNewRequest('D', $vol,array(),$keytypedaeroport);
                        $omessage->addAction($oaction);
                        break;

                    // création d'un nouveau vol
                    case 'N':
                        $oaction = $this->createActionDeleteOrNewRequest('N', $vol,array(),$keytypedaeroport);
                        $omessage->addAction($oaction);
                        break;

                    // modifications d'un vol existant
                    case 'CR':
                        //$datalines = $this->generationDataLineCancelAndReplaceRequest($vol,$voldernierconnu,array(),$keytypedaeroport);
                        $omessage = $this->addActionsDataLineCancelAndReplaceRequestToMessage($vol,$voldernierconnu,array(),$keytypedaeroport,$omessage);
                        break;
                }

                //création du message SCR et rajout à l'array message $amessages
                array_push($aomessages,$omessage);

            //}

        }

        //on renvoi le ou les messages créées
        return $aomessages;
    }

    private function createActionDeleteOrNewRequest($action,\AirCorsica\XKPlanBundle\Entity\Vol $vol,array $subIATAPeriod,$typedaeroportcoordonateur){

        //le paramètre subIATAPeriod tiens compte des sous-période, ici il ne sert pas car ont a désactivé cette fonctionnalité dans la methode generer()

        /*
        * Les blocs de la dataLine action
        * N XY123 XY1234 01JUL 30SEP 1234500 120319 CDG0700 0750CDG JJ
        * 1   2      3     4     5      6    7a 7b   8   9   10  11 12/13
        */

        $oAction = new SCRAction();

        //=====================
        // bloc1: "action code"
        //=====================
        //si l'aeroport coordonateur est celui de départ du vol à ajouter (ou effacer)
        if($typedaeroportcoordonateur == 'depart'){
            $oAction->setActioncode($action." ");
        }else{ //l'aeroport coordonateur est celui d'arrivé du vol à ajouter (ou effacer)
            $oAction->setActioncode($action);
        }

        //===========================================================================
        // bloc2: "arrival flight designator" et bloc3: "departure flight designator"
        //===========================================================================
        //si l'aeroport coordonateur est celui de départ du vol à ajouter (ou effacer)
        if($typedaeroportcoordonateur == 'depart'){

            $oAction->setArrivalflightdesignator("");
            $oAction->setDepartureflightdesignator($vol->getNumero()); //." "

        }else{ //l'aeroport coordonateur est celui d'arrivé du vol à ajouter (ou effacer)

            $oAction->setArrivalflightdesignator($vol->getNumero()); //." ";
            $oAction->setDepartureflightdesignator("");
        }

        //===================================================
        // bloc4: "start of period" et bloc5: "end of period"
        //===================================================
        if($subIATAPeriod == null){//la période de vol est incluse dans une période IATA

            $oAction->setStartofperiod(strtoupper($vol->getPeriodeDeVol()->getDateDebut()->format('dM')));
            $oAction->setEndofperiod(strtoupper($vol->getPeriodeDeVol()->getDateFin()->format('dM'))); //." "

        }else{

            $datetimeFormat = 'dM';
            $date = new \DateTime();
            $date->setTimestamp(strtotime($subIATAPeriod['debut']));
            $oAction->setStartofperiod(strtoupper($date->format($datetimeFormat)));
            $date->setTimestamp(strtotime($subIATAPeriod['fin']));
            $oAction->getEndofperiod(strtoupper($date->format($datetimeFormat))); //" ";

        }

        //=============================
        // bloc6: "day(s) of operation"
        //=============================
        $temp = implode("",$vol->getPeriodeDeVol()->getJoursDeValidite());
        $oAction->setWeekdaysofoperation(str_replace("-","0",$temp)); //" ";

        //======================================================================================
        // bloc7: "number of seats fitted (3 digits) and IATA aircraft subtype (3 alphanumeric)"
        //======================================================================================
        if("MEP" == $vol->getTypeDeVol()->getCodeType()){
            $oAction->setNumberofseats("000");
        }else{
            $oAction->setNumberofseats(sprintf('%03d',$vol->getAvion()->getTypeAvion()->getCapaciteSiege()));
        }

        $oAction->setIATAaircraftsubtype($vol->getAvion()->getTypeAvion()->getCodeIATA()); //." "

        //=================================================================================================
        // bloc8: "origin/previous station (arriving from)" et bloc9: "required arrival time in UTC"
        // ou bloc10: "required departure time in UTC" et bloc11: "next/destination station (departure to)"
        //=================================================================================================
        //si l'aeroport coordonateur est celui de départ du vol à ajouter (ou effacer)
        if($typedaeroportcoordonateur == 'depart'){

            $oAction->setArrivingfrom("");
            $oAction->setArrivaltime("");
            $oAction->setDeparturetime($vol->getPeriodeDeVol()->getDecollage()->format('Hi'));
            $oAction->setDepartureto($vol->getLigne()->getAeroportArrivee()->getCodeIATA()); //." "

        }else{ //l'aeroport coordonateur est celui d'arrivé du vol à ajouter (ou effacer)

            $oAction->setArrivingfrom($vol->getLigne()->getAeroportDepart()->getCodeIATA());
            $oAction->setArrivaltime($vol->getPeriodeDeVol()->getAtterissage()->format('Hi')); //." "
            $oAction->setDeparturetime("");
            $oAction->setDepartureto("");

        }

        ///================================================================
        // bloc12: "arrival and departure service types (may be different)"
        //=================================================================
        //si l'aeroport coordonateur est celui de départ du vol à ajouter (ou effacer)
        if($typedaeroportcoordonateur == 'depart'){
            $oAction->setDepartureservicetype($vol->getTypeDeVol()->getCodeService());
            $oAction->setArrivalservicetype("");
        }else{
            $oAction->setDepartureservicetype("");
            $oAction->setArrivalservicetype($vol->getTypeDeVol()->getCodeService());
        }

        //on renvoi la dataLine action que l'on vient de créée
        return $oAction;


    }

    private function addActionsDataLineCancelAndReplaceRequestToMessage(\AirCorsica\XKPlanBundle\Entity\Vol $vol,\AirCorsica\XKPlanBundle\Entity\VolHistorique $dernierVolDeHistoriqueAEtatConnu, array $subIATAPeriod,$typedaeroportcoordonateur, SCRMessage $omessage){

        //$dernierVolDeHistoriqueAEtatConnu sert pour l'action C Cancel
        //vol sert pour l'action R Replace
        //le paramètre subIATAPeriod tiens compte des sous-période, ici il ne sert pas car ont a désactivé cette fonctionnalité dans la methode generer()

        /*
        * Les blocs de la dataLine action
        * N XY123 XY1234 01JUL 30SEP 1234500 120319 CDG0700 0750CDG JJ
        * 1   2      3     4     5      6    7a 7b   8   9   10  11 12/13
        */

        //boucle sur les 2 lignes d'actions à générer
        for($i=1;$i<=2;$i++) {

            $oAction = new SCRAction();

            $volEnTraitement = null;

            //action code à générer
            switch ($i) {

                // C CANCEL
                case 1:
                    $action = 'C';
                    $volEnTraitement = $dernierVolDeHistoriqueAEtatConnu;
                    break;

                // R REPLACE
                case 2:
                    $action = 'R';
                    $volEnTraitement = $vol;
                    break;

            }

            //=====================
            // bloc1: "action code"
            //=====================
            //si l'aeroport coordonateur est celui de départ du vol à ajouter (ou effacer)
            if($typedaeroportcoordonateur == 'depart'){
                $oAction->setActioncode($action." ");
            }else{ //l'aeroport coordonateur est celui d'arrivé du vol à ajouter (ou effacer)
                $oAction->setActioncode($action);
            }

            //===========================================================================
            // bloc2: "arrival flight designator" et bloc3: "departure flight designator"
            //===========================================================================
            //si l'aeroport coordonateur est celui de départ du vol à ajouter (ou effacer)
            if($typedaeroportcoordonateur == 'depart'){

                $oAction->setArrivalflightdesignator("");
                $oAction->setDepartureflightdesignator($volEnTraitement->getNumero()); //." "

            }else{ //l'aeroport coordonateur est celui d'arrivé du vol à ajouter (ou effacer)

                $oAction->setArrivalflightdesignator($volEnTraitement->getNumero()); //." ";
                $oAction->setDepartureflightdesignator("");
            }

            //===================================================
            // bloc4: "start of period" et bloc5: "end of period"
            //===================================================
            //toujours la période du vol et pas du vol historique qui peut être plus grande
            if($subIATAPeriod == null){//la période de vol est incluse dans une période IATA

                $oAction->setStartofperiod(strtoupper($vol->getPeriodeDeVol()->getDateDebut()->format('dM')));
                $oAction->setEndofperiod(strtoupper($vol->getPeriodeDeVol()->getDateFin()->format('dM'))); //." "

            }else{

                $datetimeFormat = 'dM';
                $date = new \DateTime();
                $date->setTimestamp(strtotime($subIATAPeriod['debut']));
                $oAction->setStartofperiod(strtoupper($date->format($datetimeFormat)));
                $date->setTimestamp(strtotime($subIATAPeriod['fin']));
                $oAction->getEndofperiod(strtoupper($date->format($datetimeFormat))); //" ";

            }

            //=============================
            // bloc6: "day(s) of operation"
            //=============================
            //$temp = implode("",$volEnTraitement->getPeriodeDeVol()->getJoursDeValidite());
            //Les jours d'opération de la ligne C ne doivent pa être repris par rapport à la période initiale mais par rapport au jour qui subit des modification
            $temp = implode("",$vol->getPeriodeDeVol()->getJoursDeValidite());
            $oAction->setWeekdaysofoperation(str_replace("-","0",$temp)); //" ";

            //======================================================================================
            // bloc7: "number of seats fitted (3 digits) and IATA aircraft subtype (3 alphanumeric)"
            //======================================================================================
            $oAction->setNumberofseats(sprintf('%03d',$volEnTraitement->getAvion()->getTypeAvion()->getCapaciteSiege()));
            $oAction->setIATAaircraftsubtype($volEnTraitement->getAvion()->getTypeAvion()->getCodeIATA()); //." "

            //=================================================================================================
            // bloc8: "origin/previous station (arriving from)" et bloc9: "required arrival time in UTC"
            // ou bloc10: "required departure time in UTC" et bloc11: "next/destination station (departure to)"
            //=================================================================================================
            //si l'aeroport coordonateur est celui de départ du vol à ajouter (ou effacer)
            if($typedaeroportcoordonateur == 'depart'){

                $oAction->setArrivingfrom("");
                $oAction->setArrivaltime("");
                $oAction->setDeparturetime($volEnTraitement->getPeriodeDeVol()->getDecollage()->format('Hi'));
                $oAction->setDepartureto($volEnTraitement->getLigne()->getAeroportArrivee()->getCodeIATA()); //." "

            }else{ //l'aeroport coordonateur est celui d'arrivé du vol à ajouter (ou effacer)

                $oAction->setArrivingfrom($volEnTraitement->getLigne()->getAeroportDepart()->getCodeIATA());
                $oAction->setArrivaltime($volEnTraitement->getPeriodeDeVol()->getAtterissage()->format('Hi')); //." "
                $oAction->setDeparturetime("");
                $oAction->setDepartureto("");

            }

            ///================================================================
            // bloc12: "arrival and departure service types (may be different)"
            //=================================================================
            if($typedaeroportcoordonateur == 'depart'){
                $oAction->setDepartureservicetype($volEnTraitement->getTypeDeVol()->getCodeService());
                $oAction->setArrivalservicetype("");
            }else{
                $oAction->setDepartureservicetype("");
                $oAction->setArrivalservicetype($volEnTraitement->getTypeDeVol()->getCodeService());
            }

            //on enregistre la dataLine action que l'on vient de créée
            $omessage->addAction($oAction);
        }

        //on renvoi l'objet message avec les nouvelles actions
        return $omessage;
    }
























/*
 * Méthodes sur les périodes IATA
 */

    private function isDateIncludedInIATASeason($dateATester,$codeSaisonIATA){

        if($this->getPeriodIATAFromDate($dateATester) == $codeSaisonIATA){
            return true;
        }
        return false;
    }

    private function isPeriodIncludedInOneIATASeason($datedebut,$datefin){

        if( $this->getCodeSaisonIATAFromDate($datedebut) == $this->getCodeSaisonIATAFromDate($datefin) ){
            return true;
        }
        return false;
    }

    private function nextPeriodIATAFromIATACodeSaison($codeSaisonIATA){

        $abreviationSaison = substr($codeSaisonIATA, 0,1);
        $annee = substr($codeSaisonIATA, 1,2);

        if($abreviationSaison=="W"){
            $annee=strval(intval($annee)+1);
            // calcul de S
            $dateD = date_create($annee.'-03-31');
            $dateD->modify('Last Sunday');
            $dateF = date_create($annee.'-10-31');
            $dateF->modify('Last Saturday');
            $periodeIATA = array(
                'codeSaison' => "S".$annee,
                'debut'  => $dateD->format('Y-m-d'),
                'fin' => $dateF->format('Y-m-d')
            );
        }else{
            // calcul de WY
            $dateD = date_create($annee.'-10-31');
            $dateD->modify('Last Sunday');
            $dateF = date_create(strval(intval($annee)+1).'-03-31');
            $dateF->modify('Last Saturday');
            $periodeIATA = array(
                'codeSaison' => "W".$annee,
                'debut'  => $dateD->format('Y-m-d'),
                'fin' => $dateF->format('Y-m-d')
            );
        }

        return $periodeIATA;

    }

    private function nextIATACodeSaisonFromIATACodeSaison($codeSaisonIATA){
        $periodeIATA = $this->nextPeriodIATAFromIATACodeSaison($codeSaisonIATA);
        return $periodeIATA['codeSaison'];
    }

    private function getCodeSaisonIATAFromDate($date){
        $periodeIATA = $this->getPeriodIATAFromDate($date);
        return $periodeIATA['codeSaison'];
    }

    private function getPeriodFromIATASeason($codeSaisonIATA){

        $abreviationSaison = substr($codeSaisonIATA, 0,1);
        $annee = substr($codeSaisonIATA, 1,2);

        if($abreviationSaison=="W"){//hivers
            $dateD = date_create($annee.'-10-31');
            $dateD->modify('Last Sunday');
            $dateF = date_create(strval(intval($annee)+1).'-03-31');
            $dateF->modify('Last Saturday');
            $periodeIATA = array(
                'codeSaison' => "W".$annee,
                'debut'  => $dateD->format('Y-m-d'),
                'fin' => $dateF->format('Y-m-d')
            );
        }else{//été
            $dateD = date_create($annee.'-03-31');
            $dateD->modify('Last Sunday');
            $dateF = date_create($annee.'-10-31');
            $dateF->modify('Last Saturday');
            $periodeIATA = array(
                'codeSaison' => "S".$annee,
                'debut'  => $dateD->format('Y-m-d'),
                'fin' => $dateF->format('Y-m-d')
            );
        }

        return $periodeIATA;
    }

    private function getPeriodIATAFromDate($date){ //datestring au format Y-m-d
        $anneeY = substr($date, 0,4);
        // calcul de WX
        $date1D = date_create(strval(intval($anneeY)-1).'-10-31'); //dernier dimanche d'octobre
        $date1D->modify('Last Sunday');
        $date1F = date_create($anneeY.'-03-31');
        $date1F->modify('Last Saturday');
        if( strtotime($date) <= strtotime($date1F->format('Y-m-d')) ) { //dernier dimanche de mars
            $periodeIATA = array(
                'codeSaison' => "W".substr(strval(intval($anneeY)-1), 2,2),
                'debut'  => $date1D->format('Y-m-d'),
                'fin' => $date1F->format('Y-m-d')
            );
            return $periodeIATA;
        }
        // calcul de SY
        $date2D = date_create($anneeY.'-03-31');
        $date2D->modify('Last Sunday');
        $date2F = date_create($anneeY.'-10-31');
        $date2F->modify('Last Saturday');
        if( strtotime($date) <= strtotime($date2F->format('Y-m-d')) ) {
            $periodeIATA = array(
                'codeSaison' => "S".substr($anneeY, 2,2),
                'debut'  => $date2D->format('Y-m-d'),
                'fin' => $date2F->format('Y-m-d')
            );
            return $periodeIATA;
        }
        // calcul de WY
        $date3D = date_create($anneeY.'-10-31');
        $date3D->modify('Last Sunday');
        $date3F = date_create(strval(intval($anneeY)+1).'-03-31');
        $date3F->modify('Last Saturday');
        $periodeIATA = array(
            'codeSaison' => "W".substr($anneeY, 2,2),
            'debut'  => $date3D->format('Y-m-d'),
            'fin' => $date3F->format('Y-m-d')
        );
        return $periodeIATA;
    }

    private function getSubIATAPeriodsFromAPeriod($datedebut,$datefin){

        $periodeIATAQuiInclusDateDebut = $this->getPeriodIATAFromDate($datedebut);

        if( $this->isDateIncludedInIATASeason($datefin,$periodeIATAQuiInclusDateDebut['codeSaison'])){//la période récupérée $datedebut -> $datefin est inclue dans une période IATA

            $periodesIATA = array( array(
                'codeSaison' => $periodeIATAQuiInclusDateDebut['codeSaison'],
                'debut'  => $datedebut,
                'fin' => $datefin
            ) );

        }else{

            $periodesIATA = array();

            //premiére sous-période
            array_push($periodesIATA,array(
                'codeSaison' => $periodeIATAQuiInclusDateDebut['codeSaison'],
                'debut'  => $datedebut,
                'fin' => $periodeIATAQuiInclusDateDebut['fin']
            ));

            //sous-période intermediaire
            $codeSaisonIATACourante = $this->nextIATACodeSaisonFromIATACodeSaison($this->getCodeSaisonIATAFromDate($datedebut));
            while ( $codeSaisonIATACourante != $this->getCodeSaisonIATAFromDate($datefin)) {
                $intermediairePeriodeIATA = $this->getPeriodFromIATASeason($codeSaisonIATACourante);
                array_push($periodesIATA, array(
                    'debut'  => $intermediairePeriodeIATA['debut'],
                    'fin' => $intermediairePeriodeIATA['fin'],
                    'codeSaison' => $codeSaisonIATACourante
                ));
                $codeSaisonIATACourante = $this->nextIATACodeSaisonFromIATACodeSaison($codeSaisonIATACourante);
            }

            //dernière sous-période
            $dernierePeriodeIATA = $this->getPeriodFromIATASeason($codeSaisonIATACourante);
            array_push($periodesIATA, array(
                'debut'  => $dernierePeriodeIATA['debut'],
                'fin' => $datefin,
                'codeSaison' => $dernierePeriodeIATA['codeSaison']
            ));

        }

        return $periodesIATA;

    }

    private function getPeriodeSaisonIATA($annee,$saison) {

        $datetimeFormat = 'Y/m/d';
        $date = new \DateTime();

        if($saison=="hiver"){//hiver
            $dateD = date_create($annee.'-10-31');
            $dateD->modify('Last Sunday');
            $dateF = date_create(strval(intval($annee)+1).'-03-31');
            $dateF->modify('Last Saturday');
            $periodeIATA = array(
                'codeSaison' => "W".$annee,
                'debut'  => $dateD->format('Y-m-d'),
                'fin' => $dateF->format('Y-m-d')
            );
        }else{//été
            $dateD = date_create($annee.'-03-31');
            $dateD->modify('Last Sunday');
            $dateF = date_create($annee.'-10-31');
            $dateF->modify('Last Saturday');
            $periodeIATA = array(
                'codeSaison' => "S".$annee,
                'debut'  => $dateD->format('Y-m-d'),
                'fin' => $dateF->format('Y-m-d')
            );
        }

        $date->setTimestamp(strtotime($periodeIATA['debut']));
        $dateDebutSaisonIATA = $date->format($datetimeFormat);

        $date->setTimestamp(strtotime($periodeIATA['fin']));
        $dateFinSaisonIATA = $date->format($datetimeFormat);

        return new Periode($dateDebutSaisonIATA,$dateFinSaisonIATA);
    }



}

