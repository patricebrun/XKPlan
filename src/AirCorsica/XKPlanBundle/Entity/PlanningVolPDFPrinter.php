<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * PlanningVolPDFPrinter
 */
class PlanningVolPDFPrinter
{

    const CONST_NBLIGNESAVIONSMAXAVANTSAUTDEPAGE = 14; //11
    const CONST_LARGEURZEBRURECOULEURIMMO = 0.9;
    const CONST_FONTSIZELIBELLEAVIONDEFAULT = 0.75;
    const CONST_FONTSIZEHEURETRANCHEHORAIRE = 0.85;
    const CONST_FONTSIZELEGENDE = 0.75;
    const CONST_COULEURSEPARATEURBLOCAVION = "#EEEEEE";
    const CONST_COULEURCLAIREZEBRUREIMMO = "#FFBBBB";
    const CONST_COULEURFONCEEZEBRUREIMMO = "#DDAAAA";
    //const CONST_COULEURFONDAVIONS = "#41B5EE";
    const CONST_COULEURFONDAVIONS_XK_AT7 = "#41B5EE";
    const CONST_COULEURFONDAVIONS_XK_320 = "#4267ed";
    const CONST_COULEURFONDAVIONS_AFFRETE = "#ed4256";
    const CONST_FONTSIZEVILLEDEPARTARRIVEEDEFAULT = 0.45;//0.47;
    const CONST_FONTSIZEHEUREDECOLLAGEATTERISSAGEDEFAULT = 0.44;
    const CONST_FONTSIZECODEVOLDEFAULT = 0.36;//0.34;
    const CONST_FONTSIZETYPEVOLDEFAULT = 0.38;
    const CONST_CHEVAUCHEJOURVOLFONT_DIMINUTIONECHELLE = 0.06;
    const CONST_LARGEURENPOURCENTAGELEGENDEAVION = 5; //5 correspond à 5% de la largeur du document
    const CONST_LARGEURPOURCENTAGEMINIAFFICHAGEVOL = 2.60;
    const CONST_EPAISSEURTRAITDELIMITATIONDUREEDEVOL = 1.35;



    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var container
     */
    private $container;

    /**
     * @var string
     */
    private $templateId;

    /**
     * @var string
     */
    private $dateImpression;

    /**
     * @var string
     */
    private $heureImpression;

    /**
     * @var array
     */
    private $aJoursDeLaSemaine;

    /**
     * @var array
     */
    private $aIdAvionsAImprimer;

    /**
     * @var string
     */
    private $mentionHoraires;

    /**
     * @var \DateTime
     */
    private $debutPeriodeImpression;

    /**
     * @var \DateTime
     */
    private $finPeriodeImpression;

    /**
     * @var string
     */
    private $mentionSaisonEtSousSaison;

    /**
     * @var \DateTime
     */
    private $debutPeriodeSousSaison;

    /**
     * @var \DateTime
     */
    private $finPeriodeSousSaison;

    /**
     * optionImpressionAvions = 0 on imprime tous les avions qu'il y ai des vols ou nom
     * optionImpressionAvions = 1 on imprime uniquement les avions qui ont des vols ou immobilisés
     * optionImpressionAvions = 2 on imprime uniquement les avions qui ont des vols
     * optionImpressionAvions = 3 on imprime uniquement les avions qui ont été selectionnés
     * @var  int
     */
    private $optionImpressionAvions;

    /**
     * @var bool
     */
    private $impressionEnNoirEtBlanc;

    /**
     * @var bool
     */
    private $autoResizeTranchesHoraires;

    /**
     * @var bool
     */
    private $autoResizeFonts;

    /**
     * @var bool
     */
    private $reperesdurees;

    /**
     * @var bool
     */
    private $overflowTexteVolsCourts;

    /**
     * @var integer
     */
    private $dureemaxivolscourts;

    /**
     * @var bool
     */
    private $enteteCompagnie;

    /**
     * @var bool
     */
    private $enteteJourSemaine;

    /**
     * @var bool
     */
    private $enteteNumeroSemaine;

    /**
     * @var bool
     */
    private $enteteSaison;

    /**
     * @var bool
     */
    private $enteteTypeHoraire;

    /**
     * @var bool
     */
    private $enteteDateImpression;

    /**
     * @var bool
     */
    private $affichagetranchehorairejoursuivant;

    /**
     * @var bool
     */
    private $enteteNumeroDePage;

    /**
     * @var int
     */
    private $margeHaute;

    /**
     * @var int
     */
    private $margeDroite;

    /**
     * @var int
     */
    private $margeGauche;

    /**
     * @var int
     */
    private $nbDePages;

    /**
     * @var tcpdf
     */
    private $pdf;

    /**
     * PlanningVolPDFPrinter constructor.
     * @param \Doctrine\ORM\EntityManager $em
     * @param $container
     * @param Template $template
     * @param string $dateImpression
     * @param string $mentionHoraires
     * @param \DateTime $debutPeriodeImpression
     * @param \DateTime $finPeriodeImpression
     * @param string $mentionSaisonEtSousSaison
     * @param bool $impressionEnNoirEtBlanc
     * @param bool $enteteCompagnie
     * @param bool $autoResizeTranchesHoraires
     * @param int $margeGauche
     * @param int $margeHaute
     * @param int $margeDroite
     * @param bool $affichagetranchehorairejoursuivant
     */
    function __construct($em,$container,$templateId,$debutPeriodeImpression,$finPeriodeImpression,$aJoursDeLaSemaine,$aIdAvionsAImprimer,$optionImpressionAvions,$impressionEnNoirEtBlanc,$autoResizeFonts,$autoResizeTranchesHoraires,$reperesdurees,$overflowTexteVolsCourts,$dureemaxivolscourts,$enteteCompagnie,$enteteJourSemaine,$enteteNumeroSemaine,$enteteSaison,$enteteTypeHoraire,$enteteDateImpression,$enteteNumeroDePage,$margeGauche,$margeHaute,$margeDroite,$affichagetranchehorairejoursuivant)
    {
        //l'entity manager
        $this->em = $em;

        //le container
        $this->container = $container;

        //le template choisi pour l'impression
        $this->templateId = $templateId;

        //date d'impression
        $datedujour = date_create();
        $english_months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'Décember');
        $french_months = array('Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');
        $this->dateImpression = str_replace($english_months, $french_months,$datedujour->format('d F Y'));
        $this->heureImpression = $datedujour->format('h:i');

        //mention horaire
        $this->mentionHoraires = "Horaires UTC";

        //debut de la période à imprimer
        $this->debutPeriodeImpression = $debutPeriodeImpression;

        //fin de la période à imprimer
        $this->finPeriodeImpression = $finPeriodeImpression;

        //les jours de la semaines que l'on doit imprimer (dans le cas d'une période)
        $this->aJoursDeLaSemaine = $aJoursDeLaSemaine;

        //les avions choisi pour l'impression (est utilisé si $optionImpressionAvions = 3)
        $this->aIdAvionsAImprimer = $aIdAvionsAImprimer;

        //l'option définissant les avoins à imprimer
        $this->optionImpressionAvions = $optionImpressionAvions;

        //option activant la gestion de la taille de police en fonction de la durée du vol
        $this->autoResizeFonts = $autoResizeFonts;

        //l'impression est-elle ne noir et blanc
        $this->impressionEnNoirEtBlanc = $impressionEnNoirEtBlanc;

        //doit-on afficher les repères de durée des vols
        $this->reperesdurees = $reperesdurees;

        //doit-ont faire un overflow du texte hors de la case des vols courts si inférieur ou égale à CONST_DUREEVOLMAXIPOUROVERFLOWTEXTE
        $this->overflowTexteVolsCourts = $overflowTexteVolsCourts;

        //la durée ou un vol est considéré comme court
        $this->dureemaxivolscourts = $dureemaxivolscourts;

        //les en-têtes a afficher
        $this->enteteCompagnie = $enteteCompagnie;
        $this->enteteJourSemaine = $enteteJourSemaine;
        $this->enteteNumeroSemaine = $enteteNumeroSemaine;
        $this->enteteSaison = $enteteSaison;
        $this->enteteTypeHoraire = $enteteTypeHoraire;
        $this->enteteDateImpression = $enteteDateImpression;
        $this->enteteNumeroDePage = $enteteNumeroDePage;

        //recadre t'on les tranche horaire en fonction de l'existence de vol au debut et à la fin
        $this->autoResizeTranchesHoraires = $autoResizeTranchesHoraires;

        //Paramêtres d'impression par défaut
        if($margeGauche<0){
            $margeGauche = 0;
        }
        if($margeHaute<0){
            $margeHaute = 0;
        }
        if($margeDroite<0){
            $margeDroite = 0;
        }
        $this->margeGauche = $margeGauche; // 0 correspond à 0% de la largeur du document
        $this->margeHaute = $margeHaute+6; // 0 correspond à 0% de la hauteur du document(en A4: 21cm x 29,7cm), 6 correspond à la hauteur du header de la page.
        $this->margeDroite = $margeDroite; // 0 correspond à 0% de la largeur du document
        $this->nbDePages = intval(date_diff($this->debutPeriodeImpression, $this->finPeriodeImpression)->format('%R%a'))+1;

        //affiche des tranche horaire supérieur à 24h si un vol du soir se pose le lendemain
        $this->affichagetranchehorairejoursuivant = $affichagetranchehorairejoursuivant;

    }


    /**
     * Render the Html data planning for one day
     * @return string
     */
    private function htmlRenderOneDayPlanning($aDataPlanningParAvion,$aheureMini,$aheureMaxi)
    {
        $nbDeLigneMaxAvantRepetLegendeHorizontal = self::CONST_NBLIGNESAVIONSMAXAVANTSAUTDEPAGE;
        $largeurLigneCouleurImmo = self::CONST_LARGEURZEBRURECOULEURIMMO;
        $tailleLettresLegende_NomAvion = self::CONST_FONTSIZELIBELLEAVIONDEFAULT;
        $tailleLettresLegende_TrancheHoraire = self::CONST_FONTSIZEHEURETRANCHEHORAIRE;
        $tailleLettresLegende_Legende = self::CONST_FONTSIZELEGENDE;
        $couleurLigneSeparationHorizontal = self::CONST_COULEURSEPARATEURBLOCAVION;
        $couleurZebrureClaireImmobilisation = self::CONST_COULEURCLAIREZEBRUREIMMO;
        $couleurZebrureFonceImmobilisation = self::CONST_COULEURFONCEEZEBRUREIMMO;
        if($this->impressionEnNoirEtBlanc == true) {
            $couleurZebrureClaireImmobilisation = $this->hexaShadeOfGreyForHexaColor($couleurZebrureClaireImmobilisation);
            $couleurZebrureFonceImmobilisation = $this->hexaShadeOfGreyForHexaColor($couleurZebrureFonceImmobilisation);
        }

        $couleurFondAvions_XK_AT7 = self::CONST_COULEURFONDAVIONS_XK_AT7;
        $couleurFondAvions_XK_320 = self::CONST_COULEURFONDAVIONS_XK_320;
        $couleurFondAvions_AFFRETE = self::CONST_COULEURFONDAVIONS_AFFRETE;
        //$couleurFondAvions = self::CONST_COULEURFONDAVIONS;
        if($this->impressionEnNoirEtBlanc == true) {
            //$couleurFondAvions = $this->hexaShadeOfGreyForHexaColor($couleurFondAvions);
            $couleurFondAvions_XK_AT7 = $this->hexaShadeOfGreyForHexaColor($couleurFondAvions_XK_AT7);
            $couleurFondAvions_XK_320 = $this->hexaShadeOfGreyForHexaColor($couleurFondAvions_XK_320);
            $couleurFondAvions_AFFRETE = $this->hexaShadeOfGreyForHexaColor($couleurFondAvions_AFFRETE);
        }

        //taille en pourcentage de la largeur du planning vol (sans la légende des avions)
        $totalPercentPlanning = 100 - self::CONST_LARGEURENPOURCENTAGELEGENDEAVION;

        //légende horizontale
        $html='<table cellpadding="0" border="1" align="center" width="100%"><tbody>';
        $html.='<tr>';
        $html.='<td style="font-size:'.$tailleLettresLegende_Legende.'em; font-weight:bold;" width="'.self::CONST_LARGEURENPOURCENTAGELEGENDEAVION.'%">Heures / Avions</td>'; //légende avion
        $nbDeCase = intval($aheureMaxi[0]) - intval($aheureMini[0]) +1;
        for ($i = intval($aheureMini[0]); $i <= intval($aheureMaxi[0]); $i++) {
            if($i>23){
                $legendehoriz = $i-24;
            }else{
                $legendehoriz = $i;
            }
            $html.='<td width="'.round(($totalPercentPlanning/$nbDeCase),2).'%">';
            $html.='<span style="font-size:0.15em;"><br/></span>';
            $html.='<span style="font-size:'.$tailleLettresLegende_TrancheHoraire.'em; font-weight:bold;" >'.sprintf("%02d", $legendehoriz/*$i*/).'</span>';
            $html.='</td>';
        }
        $html.='</tr>';
        $html.="</tbody></table>";

        //les lignes de vols pour ce jour
        $countLigneDeLaPage = 0;
        foreach($aDataPlanningParAvion as $keyAvion=>$volsDunAvion){

            //si tous les avions ne teinnent pas sur une page
            if( ($keyAvion!=0) && (($countLigneDeLaPage % $nbDeLigneMaxAvantRepetLegendeHorizontal) == 0)){ //si la ligne courante est divisible par $nbDeLigneMaxAvantRepetLegendeHorizontal on affiche la légende
                $html.='<table cellpadding="0" border="1" align="center" width="100%" pagebreak="true"><tbody><tr>';
                //légende horizontale
                $html.='<td width="'.self::CONST_LARGEURENPOURCENTAGELEGENDEAVION.'%"></td>'; //légende avion
                $nbDeCase = intval($aheureMaxi[0]) - intval($aheureMini[0]) +1;
                for ($i = intval($aheureMini[0]); $i <= intval($aheureMaxi[0]); $i++) {
                    if($i>23){
                        $legendehoriz = $i-24;
                    }else{
                        $legendehoriz = $i;
                    }
                    $html.='<td width="'.round(($totalPercentPlanning/$nbDeCase),2).'%">';
                    $html.='<span style="font-size:0.15em;"><br/></span>';
                    $html.='<span style="font-size:'.$tailleLettresLegende_TrancheHoraire.'em; font-weight:bold;" >'.sprintf("%02d", $legendehoriz/*$i*/).'</span>';
                    $html.='</td>';
                }
                $html.="</tr></tbody></table>";
            }

            $html.='<table cellpadding="0" border="1" align="center" width="100%"><tbody>';

            //ligne des vols pour un avion
            $html.='<tr>';
            $html.='<td width="'.self::CONST_LARGEURENPOURCENTAGELEGENDEAVION.'%" ';

            //$html.='style="background-color:'.$couleurFondAvions.'; color:#FFFFFF; font-size:';
            if($volsDunAvion["avion"]["codeCompagnie"] == "XK"){ //avion appartenant à AirCorsica
                if($volsDunAvion["avion"]["type"] == "AT7"){ //les AT7 d'AirCorsica
                    $html.='style="background-color:'.$couleurFondAvions_XK_AT7.'; color:#FFFFFF; font-size:';
                }else{ //les autres avions d'AirCorsica A320
                    $html.='style="background-color:'.$couleurFondAvions_XK_320.'; color:#FFFFFF; font-size:';
                }
            }else{ //avion affrété
                $html.='style="background-color:'.$couleurFondAvions_AFFRETE.'; color:#FFFFFF; font-size:';
            }

            if($this->autoResizeFonts == true) {
                if(strlen($volsDunAvion["avion"]["nom"])>=8){
                    $tailleLettresLegende_NomAvion=self::CONST_FONTSIZELIBELLEAVIONDEFAULT-0.07;
                }elseif(strlen($volsDunAvion["avion"]["nom"])>=7){
                    $tailleLettresLegende_NomAvion=self::CONST_FONTSIZELIBELLEAVIONDEFAULT-0.03;
                }
            }
            $html.=$tailleLettresLegende_NomAvion.'em;">';
            $html.='&nbsp;<br/>'.$volsDunAvion["avion"]["nom"].'<br/>';
            if( ($volsDunAvion["avion"]["codeCompagnie"] != "XK") && ($this->impressionEnNoirEtBlanc == true) ){ //avion affrété et impression NB
                $html.= 'affrété';
            }else{ //avion affrété
                $html.='&nbsp;';
            }

            $html.='</td>'; //nom avion

            $abscisseCourante = 0;
            foreach($volsDunAvion["vols"] as $keyVol=>$unVolDeCetAvion){

                if($unVolDeCetAvion["chevauchejour"] == false) {
                    $abscisseDuProchainVol = $this->heureToXPercentCoordinate($aheureMini, $aheureMaxi, $unVolDeCetAvion["heureDepart"]);
                    $aHdepCetAv = explode(":",$unVolDeCetAvion["heureDepart"]);
                }else{
                    //modif
                    if($unVolDeCetAvion["departlaveille"] == true) {//le vol part la veille et arrice ce jour
                        $abscisseDuProchainVol = $this->heureToXPercentCoordinate($aheureMini, $aheureMaxi, "00:00:00");
                        $aHdepCetAv = explode(":", "00:00:00");
                    }else{//le vol par ce jour et arrive le lendemain
                        $abscisseDuProchainVol = $this->heureToXPercentCoordinate($aheureMini, $aheureMaxi, $unVolDeCetAvion["heureDepart"]);
                        $aHdepCetAv = explode(":",$unVolDeCetAvion["heureDepart"]);
                    }
                }

                //espace avant le premier vol
                //---------------------------
                if ( ($aheureMini[0] == $aHdepCetAv[0]) && (intval($aHdepCetAv[1])>0) ){
                    //if($aheureMini != explode(":",$unVolDeCetAvion["heureDepart"])) {

                    if ($abscisseCourante == 0) {
                        $largeurCaseEspace = $abscisseDuProchainVol;
                        $abscisseCourante = $abscisseDuProchainVol;
                        if ($volsDunAvion["avion"]["estImmobilise"] == true) {
                            $nbDeLigneContenuDansLalargeurCaseEspace = intval($largeurCaseEspace / $largeurLigneCouleurImmo);
                            $largeurTotalDesLignes = 0;
                            for ($cptL = 0; $cptL < $nbDeLigneContenuDansLalargeurCaseEspace; $cptL++) {
                                if ($cptL % 2 == 1) {//impair
                                    $html .= '<td width="' . $largeurLigneCouleurImmo . '%" style="border-bottom-color:' . $couleurLigneSeparationHorizontal . '; border-top-color:' . $couleurLigneSeparationHorizontal . ';background-color:' . $couleurZebrureClaireImmobilisation . '; border-left-color:' . $couleurZebrureClaireImmobilisation . '; border-right-color:' . $couleurZebrureClaireImmobilisation . '; color:#FFFFFF;"></td>';
                                } else {//pair
                                    $html .= '<td width="' . $largeurLigneCouleurImmo . '%" style="border-bottom-color:' . $couleurLigneSeparationHorizontal . '; border-top-color:' . $couleurLigneSeparationHorizontal . ';background-color:' . $couleurZebrureFonceImmobilisation . '; border-left-color:' . $couleurZebrureFonceImmobilisation . '; border-right-color:' . $couleurZebrureFonceImmobilisation . '; color:#FFFFFF;"></td>';
                                }
                                $largeurTotalDesLignes += $largeurLigneCouleurImmo;
                            }
                            if ($largeurTotalDesLignes != $largeurCaseEspace) {//derniere ligne qui ne fait pas 5% de large
                                if ($cptL % 2 == 1) {//impair
                                    $html .= '<td width="' . ($largeurCaseEspace - $largeurTotalDesLignes) . '%" style="border-bottom-color:' . $couleurLigneSeparationHorizontal . '; border-top-color:' . $couleurLigneSeparationHorizontal . ';background-color:' . $couleurZebrureClaireImmobilisation . '; border-left-color:' . $couleurZebrureClaireImmobilisation . '; border-right-color:' . $couleurZebrureClaireImmobilisation . '; color:#FFFFFF;"></td>';
                                } else {//pair
                                    $html .= '<td width="' . ($largeurCaseEspace - $largeurTotalDesLignes) . '%" style="border-bottom-color:' . $couleurLigneSeparationHorizontal . '; border-top-color:' . $couleurLigneSeparationHorizontal . ';background-color:' . $couleurZebrureFonceImmobilisation . '; border-left-color:' . $couleurZebrureFonceImmobilisation . '; border-right-color:' . $couleurZebrureFonceImmobilisation . '; color:#FFFFFF;"></td>';
                                }
                            }
                        } else {
                            $html .= '<td width="' . $largeurCaseEspace . '%" style="border-left-color:#ffffff; border-bottom-color:' . $couleurLigneSeparationHorizontal . '; border-top-color:' . $couleurLigneSeparationHorizontal . ';"></td>';
                        }
                    }

                }
                    //espace entre les vols
                    //---------------------
                    if ($abscisseCourante < $abscisseDuProchainVol) {
                        $largeurCaseEspace = $abscisseDuProchainVol - $abscisseCourante;
                        $abscisseCourante = $abscisseDuProchainVol;
                        if($volsDunAvion["avion"]["estImmobilise"] == true){
                                $nbDeLigneContenuDansLalargeurCaseEspace = intval($largeurCaseEspace/$largeurLigneCouleurImmo);
                                $largeurTotalDesLignes = 0;
                                for($cptL=0;$cptL<$nbDeLigneContenuDansLalargeurCaseEspace;$cptL++){
                                    if ($cptL%2 == 1) {//impair
                                        $html .= '<td width="' . $largeurLigneCouleurImmo . '%" style="border-bottom-color:'.$couleurLigneSeparationHorizontal.'; border-top-color:'.$couleurLigneSeparationHorizontal.';background-color:'.$couleurZebrureClaireImmobilisation.'; border-left-color:'.$couleurZebrureClaireImmobilisation.'; border-right-color:'.$couleurZebrureClaireImmobilisation.'; color:#FFFFFF;"></td>';
                                    }else{//pair
                                        $html .= '<td width="' . $largeurLigneCouleurImmo . '%" style="border-bottom-color:'.$couleurLigneSeparationHorizontal.'; border-top-color:'.$couleurLigneSeparationHorizontal.';background-color:'.$couleurZebrureFonceImmobilisation.'; border-left-color:'.$couleurZebrureFonceImmobilisation.'; border-right-color:'.$couleurZebrureFonceImmobilisation.'; color:#FFFFFF;"></td>';
                                    }
                                    $largeurTotalDesLignes+=$largeurLigneCouleurImmo;
                                }
                                if($largeurTotalDesLignes!=$largeurCaseEspace){//derniere ligne qui ne fait pas 5% de large
                                    if ($cptL%2 == 1) {//impair
                                        $html .= '<td width="' . ($largeurCaseEspace-$largeurTotalDesLignes) . '%" style="border-bottom-color:'.$couleurLigneSeparationHorizontal.'; border-top-color:'.$couleurLigneSeparationHorizontal.';background-color:'.$couleurZebrureClaireImmobilisation.'; border-left-color:'.$couleurZebrureClaireImmobilisation.'; border-right-color:'.$couleurZebrureClaireImmobilisation.'; color:#FFFFFF;"></td>';
                                    }else{//pair
                                        $html .= '<td width="' . ($largeurCaseEspace-$largeurTotalDesLignes) . '%" style="border-bottom-color:'.$couleurLigneSeparationHorizontal.'; border-top-color:'.$couleurLigneSeparationHorizontal.';background-color:'.$couleurZebrureFonceImmobilisation.'; border-left-color:'.$couleurZebrureFonceImmobilisation.'; border-right-color:'.$couleurZebrureFonceImmobilisation.'; color:#FFFFFF;"></td>';
                                    }
                                }
                        }else{
                            $html .= '<td width="' . $largeurCaseEspace . '%" style="border-left-color:#ffffff; border-bottom-color:'.$couleurLigneSeparationHorizontal.'; border-top-color:'.$couleurLigneSeparationHorizontal.';"></td>';
                        }
                    }
                //}

                //le vol courant
                //--------------
                if($unVolDeCetAvion["chevauchejour"] == false) {
                    $largeurCaseVol = $this->dureeVolToXPercentLength($aheureMini, $aheureMaxi, $unVolDeCetAvion["heureDepart"], $unVolDeCetAvion["heureArrivee"]);
                }else{
                    if($unVolDeCetAvion["departlaveille"] == true) {//le vol part la veille et arrice ce jour
                        $largeurCaseVol = $this->dureeVolToXPercentLength($aheureMini, $aheureMaxi, "00:00:00", $unVolDeCetAvion["heureArrivee"]);
                    }else {//le vol par ce jour et arrive le lendemain

                        if($this->affichagetranchehorairejoursuivant == false) {
                            /*
                            //transformer l'heure d'arrivée en 01:45:00 en 01+23
                            $aHa = explode(":", $unVolDeCetAvion["heureArrivee"]);
                            $Harr = intval($aHa[0]) + 23;
                            $heureArriveeAdaptee = $Harr . ":" . $aHa[1] . ":" . $aHa[2];
                            $largeurCaseVol = $this->dureeVolToXPercentLength($aheureMini, $aheureMaxi, $unVolDeCetAvion["heureDepart"], $heureArriveeAdaptee);
                            */
                            $largeurCaseVol = $this->dureeVolToXPercentLength($aheureMini, $aheureMaxi, $unVolDeCetAvion["heureDepart"], "23:59:59");

                        }else{

                            //transformer l'heure d'arrivée en 01:45:00 en 25:45:00
                            $aHa = explode(":", $unVolDeCetAvion["heureArrivee"]);
                            $Harr = intval($aHa[0]) + 24;
                            $heureArriveeAdaptee = $Harr . ":" . $aHa[1] . ":" . $aHa[2];
                            $largeurCaseVol = $this->dureeVolToXPercentLength($aheureMini, $aheureMaxi, $unVolDeCetAvion["heureDepart"], $heureArriveeAdaptee);
                        }
                    }

                }

                if($this->autoResizeFonts == true) {

                    //changement de la taille de la police en fonction de la durée du vol
                    if ($largeurCaseVol >= 6) {
                        $tailleLettresVilleDecollageEtAtterrissage = self::CONST_FONTSIZEVILLEDEPARTARRIVEEDEFAULT+0.24;
                        $tailleLettresHorairesDecollageEtAtterrissage = self::CONST_FONTSIZEHEUREDECOLLAGEATTERISSAGEDEFAULT+0.22;
                        $tailleLettresCodeVol = self::CONST_FONTSIZECODEVOLDEFAULT+0.22;
                        $tailleLettresTypeVol = self::CONST_FONTSIZETYPEVOLDEFAULT+0.22;
                    }elseif ($largeurCaseVol >= 5.5 && $largeurCaseVol < 6) {
                        $tailleLettresVilleDecollageEtAtterrissage = self::CONST_FONTSIZEVILLEDEPARTARRIVEEDEFAULT+0.19;
                        $tailleLettresHorairesDecollageEtAtterrissage = self::CONST_FONTSIZEHEUREDECOLLAGEATTERISSAGEDEFAULT+0.17;
                        $tailleLettresCodeVol = self::CONST_FONTSIZECODEVOLDEFAULT+0.18;
                        $tailleLettresTypeVol = self::CONST_FONTSIZETYPEVOLDEFAULT+0.18;
                    }elseif ($largeurCaseVol > 4.5 && $largeurCaseVol < 5.5) {
                        $tailleLettresVilleDecollageEtAtterrissage = self::CONST_FONTSIZEVILLEDEPARTARRIVEEDEFAULT+0.16;
                        $tailleLettresHorairesDecollageEtAtterrissage = self::CONST_FONTSIZEHEUREDECOLLAGEATTERISSAGEDEFAULT+0.16;
                        $tailleLettresCodeVol = self::CONST_FONTSIZECODEVOLDEFAULT+0.16;
                        $tailleLettresTypeVol = self::CONST_FONTSIZETYPEVOLDEFAULT+0.16;
                    } elseif ($largeurCaseVol <= 4.5 && $largeurCaseVol > 4) {
                        $tailleLettresVilleDecollageEtAtterrissage = self::CONST_FONTSIZEVILLEDEPARTARRIVEEDEFAULT+0.125;
                        $tailleLettresHorairesDecollageEtAtterrissage = self::CONST_FONTSIZEHEUREDECOLLAGEATTERISSAGEDEFAULT+0.14;
                        $tailleLettresCodeVol = self::CONST_FONTSIZECODEVOLDEFAULT+0.115;
                        $tailleLettresTypeVol = self::CONST_FONTSIZETYPEVOLDEFAULT+0.115;
                    } elseif ($largeurCaseVol <= 4 && $largeurCaseVol > 3.5) {
                        $tailleLettresVilleDecollageEtAtterrissage = self::CONST_FONTSIZEVILLEDEPARTARRIVEEDEFAULT+0.06;
                        $tailleLettresHorairesDecollageEtAtterrissage = self::CONST_FONTSIZEHEUREDECOLLAGEATTERISSAGEDEFAULT+0.13;
                        $tailleLettresCodeVol = self::CONST_FONTSIZECODEVOLDEFAULT+0.065;
                        $tailleLettresTypeVol = self::CONST_FONTSIZETYPEVOLDEFAULT+0.065;
                    } elseif ($largeurCaseVol <= 3.5 && $largeurCaseVol > 0) {
                        $tailleLettresVilleDecollageEtAtterrissage = self::CONST_FONTSIZEVILLEDEPARTARRIVEEDEFAULT+0.03;
                        $tailleLettresHorairesDecollageEtAtterrissage = self::CONST_FONTSIZEHEUREDECOLLAGEATTERISSAGEDEFAULT+0.04;
                        $tailleLettresCodeVol = self::CONST_FONTSIZECODEVOLDEFAULT+0.01;
                        $tailleLettresTypeVol = self::CONST_FONTSIZETYPEVOLDEFAULT+0.01;
                    }

                }else{

//                    if ($largeurCaseVol <= 4){
//                        $tailleLettresVilleDecollageEtAtterrissage = self::CONST_FONTSIZEVILLEDEPARTARRIVEEDEFAULT;
//                        $tailleLettresHorairesDecollageEtAtterrissage = self::CONST_FONTSIZEHEUREDECOLLAGEATTERISSAGEDEFAULT;
//                        $tailleLettresCodeVol = self::CONST_FONTSIZECODEVOLDEFAULT;
//                        $tailleLettresTypeVol = self::CONST_FONTSIZETYPEVOLDEFAULT;
//                    }else{
//                        $tailleLettresVilleDecollageEtAtterrissage = self::CONST_FONTSIZEVILLEDEPARTARRIVEEDEFAULT+0.125;
//                        $tailleLettresHorairesDecollageEtAtterrissage = self::CONST_FONTSIZEHEUREDECOLLAGEATTERISSAGEDEFAULT+0.14;
//                        $tailleLettresCodeVol = self::CONST_FONTSIZECODEVOLDEFAULT+0.115;
//                        $tailleLettresTypeVol = self::CONST_FONTSIZETYPEVOLDEFAULT+0.115;
//                    }

                    $tailleLettresVilleDecollageEtAtterrissage = self::CONST_FONTSIZEVILLEDEPARTARRIVEEDEFAULT+0.06;
                    $tailleLettresHorairesDecollageEtAtterrissage = self::CONST_FONTSIZEHEUREDECOLLAGEATTERISSAGEDEFAULT+0.07;
                    $tailleLettresCodeVol = self::CONST_FONTSIZECODEVOLDEFAULT+0.06;
                    $tailleLettresTypeVol = self::CONST_FONTSIZETYPEVOLDEFAULT+0.06;

                }


                $html.='<td width="'.$largeurCaseVol.'%" style="border-bottom-color:'.$couleurLigneSeparationHorizontal.';';
                if($this->impressionEnNoirEtBlanc == false){//impression en couleur

                    if(strtoupper ($unVolDeCetAvion["backgroundColor"]) != "#FFFFFF"){//si une couleur de fond est définie
                    $html.='background-color:'.$unVolDeCetAvion["backgroundColor"].';';
                    }else{//si il n'y a pas de couleur de fond
                        //$html.='border-left-color:#000000; border-right-color:#000000;';
                    }

                }else{//impression en nb

                    if(strtoupper ($unVolDeCetAvion["backgroundColor"]) != "#FFFFFF"){//si une couleur de fond est définie on la transforme en niveau de gris
                        $html.='background-color:'.$this->hexaShadeOfGreyForHexaColor($unVolDeCetAvion["backgroundColor"]).';';
                    }else{//si il n'y a pas de couleur de fond
                        //$html.='border-left-color:#000000; border-right-color:#000000;';
                    }

                }
                $html.='">';

                // Repéres de durée des vol
                // lignes noir épaisse délimitant ce vol
                //---------------------------
                if($this->reperesdurees == true) {

                    $html .= '<table cellpadding="0" cellspacing="0.5" border="0" width="100%"><tbody>';
                    $html .= '<tr><td style="line-height:1em;"></td></tr>';
                    $html .= '<tr>';
                    $html .= '<td style="background-color:#000000;line-height: ' . self::CONST_EPAISSEURTRAITDELIMITATIONDUREEDEVOL . 'em;"></td>';
                    $html .= '</tr>';
                    $html .= '</tbody></table>';

                }

                //Information de la cellule Vol
                //-----------------------------
                if($largeurCaseVol>self::CONST_LARGEURPOURCENTAGEMINIAFFICHAGEVOL) {// cas générale

                    $html .= '<table cellpadding="1" cellspacing="0" border="0" ';
                    if( ($this->overflowTexteVolsCourts == true) && ($this->isVolUnVolCourt($unVolDeCetAvion["heureDepart"],$unVolDeCetAvion["heureArrivee"],intval($this->dureemaxivolscourts)) == true) ){
                        if($this->autoResizeFonts == true) {
                            $html .= 'width="127%"';
                        }else{
                            $html .= 'width="150%"';
                        }
                    }else{
                        $html.='width="100%"';
                    }
                    $html.='><tbody>';
                    $html .= '<tr style="width:inherit !important;">';
                    $html .= '<td valign="top" style="text-align:left; font-size:' . $tailleLettresVilleDecollageEtAtterrissage . 'em; font-weight:bold;">'. $unVolDeCetAvion["villeDepart"] . '</td>';
                    //trait entre depart destionation du genre de la V1
                    /*if($this->reperesdurees == false){
                        $html .= '<td style="line-height: ' . self::CONST_EPAISSEURTRAITDELIMITATIONDUREEDEVOL . 'em;"><div></div><div style="background-color:#000000;height:2px;"></div></td>';
                    }*/
                    $html .= '<td valign="top" style="text-align:right; font-size:' . $tailleLettresVilleDecollageEtAtterrissage . 'em; font-weight:bold;">' . $unVolDeCetAvion["villeArrivee"] . '</td>';
                    $html .= '</tr>';
                    $html .= '</tbody></table>';

                    $html .= '<table cellpadding="1" cellspacing="0" border="0" ';
                    if( ($this->overflowTexteVolsCourts == true) && ($this->isVolUnVolCourt($unVolDeCetAvion["heureDepart"],$unVolDeCetAvion["heureArrivee"],intval($this->dureemaxivolscourts)) == true) ){
                        if($this->autoResizeFonts == true) {
                            $html .= 'width="127%"';
                        }else{
                            $html .= 'width="150%"';
                        }
                    }else{
                        $html.='width="100%"';
                    }
                    $html .= '><tbody>';
                    $html .= '<tr>';
                    $html .= '<td valign="top" style="text-align:left; font-size:' . $tailleLettresHorairesDecollageEtAtterrissage . 'em;">' . explode(":", $unVolDeCetAvion["heureDepart"])[0] . '</td>';
                    $html .= '<td valign="top" style="text-align:center; font-size:' . $tailleLettresCodeVol . 'em;">' . substr($unVolDeCetAvion["codeVol"],0,2) . '</td>';
                    $html .= '<td valign="top" style="text-align:right; font-size:' . $tailleLettresHorairesDecollageEtAtterrissage . 'em;">' . explode(":", $unVolDeCetAvion["heureArrivee"])[0] . '</td>';
                    $html .= '</tr>';
                    $html .= '</tbody></table>';

                    $html .= '<table cellpadding="1" cellspacing="0" border="0" ';
                    if( ($this->overflowTexteVolsCourts == true) && ($this->isVolUnVolCourt($unVolDeCetAvion["heureDepart"],$unVolDeCetAvion["heureArrivee"],intval($this->dureemaxivolscourts)) == true) ){
                        if($this->autoResizeFonts == true) {
                            $html .= 'width="127%"';
                        }else{
                            $html .= 'width="150%"';
                        }
                    }else{
                        $html.='width="100%"';
                    }
                    $html .= '><tbody>';
                    $html .= '<tr>';
                    $html .= '<td valign="top" style="text-align:left; font-size:' . $tailleLettresHorairesDecollageEtAtterrissage . 'em;">' . explode(":", $unVolDeCetAvion["heureDepart"])[1] . '</td>';
                    $html .= '<td valign="top" style="text-align:center; font-size:' . $tailleLettresCodeVol . 'em;">' . substr($unVolDeCetAvion["codeVol"],2) . '</td>';
                    $html .= '<td valign="top" style="text-align:right; font-size:' . $tailleLettresHorairesDecollageEtAtterrissage . 'em;">' . explode(":", $unVolDeCetAvion["heureArrivee"])[1] . '</td>';
                    $html .= '</tr>';
                    $html .= '</tbody></table>';

                }else{// la case est trop petite généralement chevauchement de jour et durée restante de vol négligeable


                    //on affiche une case qui déborde de l'échelle de temps
                    if( ($this->overflowTexteVolsCourts == true) && ($this->isVolUnVolCourt($unVolDeCetAvion["heureDepart"],$unVolDeCetAvion["heureArrivee"],intval($this->dureemaxivolscourts)) == true) ){

                        $html .= '<table cellpadding="1" cellspacing="0" border="0" ';
                        if($this->autoResizeFonts == true) {
                            $html .= 'width="205%"';
                        }else{
                            $html .= 'width="225%"';
                        }
                        $html.='><tbody>';
                        $html .= '<tr style="width:inherit !important;">';
                        $html .= '<td valign="top" style="text-align:left; font-size:' . $tailleLettresVilleDecollageEtAtterrissage . 'em; font-weight:bold;">'. $unVolDeCetAvion["villeDepart"] . '</td>';
                        //trait entre depart destionation du genre de la V1
                        /*if($this->reperesdurees == false){
                            $html .= '<td style="line-height: ' . self::CONST_EPAISSEURTRAITDELIMITATIONDUREEDEVOL . 'em;"><div></div><div style="background-color:#000000;height:2px;"></div></td>';
                        }*/
                        $html .= '<td valign="top" style="text-align:right; font-size:' . $tailleLettresVilleDecollageEtAtterrissage . 'em; font-weight:bold;">' . $unVolDeCetAvion["villeArrivee"] . '</td>';
                        $html .= '</tr>';
                        $html .= '</tbody></table>';

                        $html .= '<table cellpadding="1" cellspacing="0" border="0" ';
                        if($this->autoResizeFonts == true) {
                            $html .= 'width="205%"';
                        }else{
                            $html .= 'width="225%"';
                        }
                        $html .= '><tbody>';
                        $html .= '<tr>';
                        $html .= '<td valign="top" style="text-align:left; font-size:' . $tailleLettresHorairesDecollageEtAtterrissage . 'em;">' . explode(":", $unVolDeCetAvion["heureDepart"])[0] . '</td>';
                        $html .= '<td valign="top" style="text-align:center; font-size:' . $tailleLettresCodeVol . 'em;">' . substr($unVolDeCetAvion["codeVol"],0,2) . '</td>';
                        $html .= '<td valign="top" style="text-align:right; font-size:' . $tailleLettresHorairesDecollageEtAtterrissage . 'em;">' . explode(":", $unVolDeCetAvion["heureArrivee"])[0] . '</td>';
                        $html .= '</tr>';
                        $html .= '</tbody></table>';

                        $html .= '<table cellpadding="1" cellspacing="0" border="0" ';
                        if($this->autoResizeFonts == true) {
                            $html .= 'width="205%"';
                        }else{
                            $html .= 'width="225%"';
                        }
                        $html .= '><tbody>';
                        $html .= '<tr>';
                        $html .= '<td valign="top" style="text-align:left; font-size:' . $tailleLettresHorairesDecollageEtAtterrissage . 'em;">' . explode(":", $unVolDeCetAvion["heureDepart"])[1] . '</td>';
                        $html .= '<td valign="top" style="text-align:center; font-size:' . $tailleLettresCodeVol . 'em;">' . substr($unVolDeCetAvion["codeVol"],2) . '</td>';
                        $html .= '<td valign="top" style="text-align:right; font-size:' . $tailleLettresHorairesDecollageEtAtterrissage . 'em;">' . explode(":", $unVolDeCetAvion["heureArrivee"])[1] . '</td>';
                        $html .= '</tr>';
                        $html .= '</tbody></table>';

                    }else{
                        //on affiche une case vide si on ne doit pas dépasser de la case

                        $html .= '<table cellpadding="1" cellspacing="0" border="0" width="100%"><tbody>';
                        $html .= '<tr>';
                        $html .= '<td valign="top" style="text-align:left; font-size:' . $tailleLettresVilleDecollageEtAtterrissage . 'em; font-weight:bold;"></td>';
                        $html .= '<td valign="top" style="text-align:right; font-size:' . $tailleLettresVilleDecollageEtAtterrissage . 'em; font-weight:bold;"></td>';
                        $html .= '</tr>';
                        $html .= '</tbody></table>';
                        $html .= '<table cellpadding="1" cellspacing="0" border="0" width="100%"><tbody>';
                        $html .= '<tr>';
                        $html .= '<td valign="top" style="text-align:left; font-size:' . $tailleLettresHorairesDecollageEtAtterrissage . 'em;"></td>';
                        $html .= '<td valign="top" style="text-align:center; font-size:' . $tailleLettresCodeVol . 'em;"></td>';
                        $html .= '<td valign="top" style="text-align:right; font-size:' . $tailleLettresHorairesDecollageEtAtterrissage . 'em;"></td>';
                        $html .= '</tr>';
                        $html .= '</tbody></table>';
                        $html .= '<table cellpadding="1" cellspacing="0" border="0" width="100%"><tbody>';
                        $html .= '<tr>';
                        $html .= '<td valign="top" style="text-align:left; font-size:' . $tailleLettresHorairesDecollageEtAtterrissage . 'em;"></td>';
                        $html .= '<td valign="top" style="text-align:center; font-size:' . $tailleLettresCodeVol . 'em;"></td>';
                        $html .= '<td valign="top" style="text-align:right; font-size:' . $tailleLettresHorairesDecollageEtAtterrissage . 'em;"></td>';
                        $html .= '</tr>';
                        $html .= '</tbody></table>';
                    }


                }


                $html.='</td>';
                $abscisseCourante += $largeurCaseVol;

                //$html.='<td>hd='.$unVolDeCetAvion["heureDepart"].'<br/> x='.$this->heureToXPercentCoordinate($aheureMini,$aheureMaxi,$unVolDeCetAvion["heureDepart"]).'%<br/> w='.$this->dureeVolToXPercentLength($aheureMini,$aheureMaxi,$unVolDeCetAvion["heureDepart"],$unVolDeCetAvion["heureArrivee"]).'%</td>';
            }
            //espace entre le dernier vol et la fin de la derniére heure
            //----------------------------------------------------------
            if($abscisseCourante<100){
                $largeurCaseEspace = (100-self::CONST_LARGEURENPOURCENTAGELEGENDEAVION)-$abscisseCourante;
                if($volsDunAvion["avion"]["estImmobilise"] == true){
                        $nbDeLigneContenuDansLalargeurCaseEspace = intval($largeurCaseEspace/$largeurLigneCouleurImmo);
                        $largeurTotalDesLignes = 0;
                        for($cptL=0;$cptL<$nbDeLigneContenuDansLalargeurCaseEspace;$cptL++){
                            if ($cptL%2 == 1) {//impair
                                $html .= '<td width="' . $largeurLigneCouleurImmo . '%" style="border-bottom-color:'.$couleurLigneSeparationHorizontal.'; border-top-color:'.$couleurLigneSeparationHorizontal.';background-color:'.$couleurZebrureClaireImmobilisation.'; border-left-color:'.$couleurZebrureClaireImmobilisation.'; border-right-color:'.$couleurZebrureClaireImmobilisation.'; color:#FFFFFF;"></td>';
                            }else{//pair
                                $html .= '<td width="' . $largeurLigneCouleurImmo . '%" style="border-bottom-color:'.$couleurLigneSeparationHorizontal.'; border-top-color:'.$couleurLigneSeparationHorizontal.';background-color:'.$couleurZebrureFonceImmobilisation.'; border-left-color:'.$couleurZebrureFonceImmobilisation.'; border-right-color:'.$couleurZebrureFonceImmobilisation.'; color:#FFFFFF;"></td>';
                            }
                            $largeurTotalDesLignes+=$largeurLigneCouleurImmo;
                        }
                        if($largeurTotalDesLignes!=$largeurCaseEspace){//derniere ligne qui ne fait pas 5% de large
                            if ($cptL%2 == 1) {//impair
                                $html .= '<td width="' . ($largeurCaseEspace-$largeurTotalDesLignes) . '%" style="border-bottom-color:'.$couleurLigneSeparationHorizontal.'; border-top-color:'.$couleurLigneSeparationHorizontal.';background-color:'.$couleurZebrureClaireImmobilisation.'; border-left-color:'.$couleurZebrureClaireImmobilisation.'; border-right-color:'.$couleurZebrureClaireImmobilisation.'; color:#FFFFFF;"></td>';
                            }else{//pair
                                $html .= '<td width="' . ($largeurCaseEspace-$largeurTotalDesLignes) . '%" style="border-bottom-color:'.$couleurLigneSeparationHorizontal.'; border-top-color:'.$couleurLigneSeparationHorizontal.';background-color:'.$couleurZebrureFonceImmobilisation.'; border-left-color:'.$couleurZebrureFonceImmobilisation.'; border-right-color:'.$couleurZebrureFonceImmobilisation.'; color:#FFFFFF;"></td>';
                            }
                        }
                }else{
                    $html .= '<td width="' . $largeurCaseEspace . '%" style="border-right-color:#ffffff; border-left-color:#ffffff; border-bottom-color:'.$couleurLigneSeparationHorizontal.'; border-top-color:'.$couleurLigneSeparationHorizontal.';"></td>';
                }
            }
            $html.='</tr>';
            $countLigneDeLaPage++;
            $html.="</tbody></table>";
        }

        return $html;
    }


    /**
     * Return the length in % de la case vol en fonction de sa durée
     */
    private function dureeVolToXPercentLength($aheureMini,$aheureMaxi,$heureDepart,$heureArrivee){

        //taille en pourcentage de la largeur du planning vol (sans la légende des avions)
        $totalPercentPlanning = 100 - self::CONST_LARGEURENPOURCENTAGELEGENDEAVION;

        //transformation de l'interval représentatif du plannigg: heure mini/maxi en int
        $heureMiniOuOrigine = intval($aheureMini[0]);
        $heureMaxi = intval($aheureMaxi[0]);

        //nombre d'heure correspondant au $totalPercentPlanning%
        $totalHeurePlanning = ($heureMaxi+1) - $heureMiniOuOrigine; //la représentation de la derniére heure dure 1 heure donc le +1

        //on explose $heureDepart en heures/minutes/secondes
        $heureDepartAPositionner = explode(":",$heureDepart);
        //on transtype les minutes de l'heure depart en heures
        $minutesDepartFloat = ($heureDepartAPositionner[1]/60);
        //on transtype l'horaire à positionner en float
        $horaireFloatDepartAPositionner = (floatval($heureDepartAPositionner[0])+$minutesDepartFloat);

        //on explose $heureArrivee en heures/minutes/secondes
        $heureArriveeAPositionner = explode(":",$heureArrivee);
        //on transtype les minutes de l'heure depart en heures
        $minutesArriveeFloat = ($heureArriveeAPositionner[1]/60);
        //on transtype l'horaire à positionner en float
        $horaireArriveeFloatAPositionner = (floatval($heureArriveeAPositionner[0])+$minutesArriveeFloat);

        //on calcul le pourcentage correspondant à cette valeur
        $calc = (($horaireArriveeFloatAPositionner-$horaireFloatDepartAPositionner)*$totalPercentPlanning)/$totalHeurePlanning;
        $xPercentLength = round($calc,2);//on arrondis le float à 2 chiffre aprés la virgule

        //on retourne la longueur
        return $xPercentLength;
    }

    /**
     * Return vol vourt ou non
     */
    private function isVolUnVolCourt($heureDepart,$heureArrivee,$dureeMaxiPourEtreUnVolCourt){

        $result = false;

        //verifiacation que ce n'est pas un vol qui chevauche au lendemain
        $testHeurDep = implode("",explode(":",$heureDepart));/*explode(":",$heureDepart)[0];*/
        $testHeurArr = implode("",explode(":",$heureArrivee));/*explode(":",$heureArrivee)[0];*/

        if(intval($testHeurDep)<intval($testHeurArr)) {// c'est un vol normal qui ne chevauche pas

            //on explose $heureDepart en heures/minutes/secondes
            $heureDepartAPositionner = explode(":", $heureDepart);
            //on transtype les minutes de l'heure depart en heures
            $minutesDepartFloat = ($heureDepartAPositionner[1] / 60);
            //on transtype l'horaire à positionner en float
            $horaireFloatDepart = (floatval($heureDepartAPositionner[0]) + $minutesDepartFloat);

            //on explose $heureArrivee en heures/minutes/secondes
            $heureArriveeAPositionner = explode(":", $heureArrivee);
            //on transtype les minutes de l'heure depart en heures
            $minutesArriveeFloat = ($heureArriveeAPositionner[1] / 60);
            //on transtype l'horaire à positionner en float
            $horaireArriveeFloat = (floatval($heureArriveeAPositionner[0]) + $minutesArriveeFloat);

            //on calcul le nombre de minute du vol
            $calc = intval(($horaireArriveeFloat - $horaireFloatDepart) * 60);

            if ($calc <= $dureeMaxiPourEtreUnVolCourt) {
                $result = true;
            }

        }

        //on retourne true si c'est un vol court
        return $result;
    }

    /**
     * Return duree du vol en minute
     */
    private function dureeVolEnMiniutes($heureDepart,$heureArrivee){

        //on explose $heureDepart en heures/minutes/secondes
        $heureDepartAPositionner = explode(":",$heureDepart);
        //on transtype les minutes de l'heure depart en heures
        $minutesDepartFloat = ($heureDepartAPositionner[1]/60);
        //on transtype l'horaire à positionner en float
        $horaireFloatDepart = (floatval($heureDepartAPositionner[0])+$minutesDepartFloat);

        //on explose $heureArrivee en heures/minutes/secondes
        $heureArriveeAPositionner = explode(":",$heureArrivee);
        //on transtype les minutes de l'heure depart en heures
        $minutesArriveeFloat = ($heureArriveeAPositionner[1]/60);
        //on transtype l'horaire à positionner en float
        $horaireArriveeFloat = (floatval($heureArriveeAPositionner[0])+$minutesArriveeFloat);

        //on retourne le nombre de minute du vol
        return intval(($horaireArriveeFloat-$horaireFloatDepart)*60);
    }

    /**
     * Return the x coordinate in %, en fonction de l'heureMni(origine) et l'heureMaxi(heureMaxi-heureMini = 100%)
     */
    private function heureToXPercentCoordinate($aheureMini,$aheureMaxi,$heureToTransform){

        //taille en pourcentage de la largeur du planning vol (sans la légende des avions)
        $totalPercentPlanning = 100 - self::CONST_LARGEURENPOURCENTAGELEGENDEAVION;

        //transformation de l'interval représentatif du plannigg: heure mini/maxi en int
        $heureMiniOuOrigine = intval($aheureMini[0]);
        $heureMaxi = intval($aheureMaxi[0]);

        //nombre d'heure correspondant au $totalPercentPlanning%
        $totalHeurePlanning = ($heureMaxi+1) - $heureMiniOuOrigine; //la représentation de la derniére heure dure 1 heure donc le +1

        //on explose $heureToTransform en heures/minutes/secondes
        $heureAPositionner = explode(":",$heureToTransform);

        //on transtype les minutes en heures
        $minutesFloat = ($heureAPositionner[1]/60);

        //on transtype l'horaire à positionner en float
        $horaireFloatAPositionner = (floatval($heureAPositionner[0])+$minutesFloat);

        //on calcul le pourcentage correspondant à cette valeur
        $calc = (($horaireFloatAPositionner-$heureMiniOuOrigine)*$totalPercentPlanning)/$totalHeurePlanning;
        $xPercentCoordinate = round($calc,2);//on arrondis le float à 2 chiffre aprés la virgule

        //on retourne le résultat
        return $xPercentCoordinate;
    }


    /**
     * Return the hexaGrey Color equivalent d'intensité equivalente of the hexaColor (pour l'impression en Noir et Blanc)
     */
    private function hexaShadeOfGreyForHexaColor($hexaColor)
    {
        //on converti la couleur hexa en couleur rbv
        list($r, $v, $b) = sscanf($hexaColor, "#%02x%02x%02x");

        //on calcul la moyenne de l'intensité des couleurs
        $moyenecouleur = intval(($r+$v+$b)/3);

        //on en déduit la teinte de gris
        $shadeofgrey = sprintf("#%02x%02x%02x", $moyenecouleur, $moyenecouleur, $moyenecouleur);
        //# - the literal character #
        //% - start of conversion specification
        //0 - character to be used for padding
        //2 - minimum number of characters the conversion should result in, padded with the above as necessary
        //x - the argument is treated as an integer and presented as a hexadecimal number with lowercase letters
        //%02x%02x - the above four repeated twice more

        return $shadeofgrey;
    }

    /**
     * Return the array sorted by $col
     */
    private function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
    $sort_col = array();
    foreach ($arr as $key=> $row) {
        $sort_col[$key] = $row[$col];
    }

    array_multisort($sort_col, $dir, $arr);
}


    /**
     * Return the pdf for printing
     */
    public function createPDF(Template $template)
    {
        //variables
        //$aDataPlanningParAvion = array(); //contiendra la génération du tableau de data utilisé par le render Html
        $dateDuJour = $this->debutPeriodeImpression;
        $dateDuJour->setTime(0,0,0);
        $jourCourantPlusUn = date_create();

        //creation d'un object TCPDF
        $this->pdf = $this->container->get("white_october.tcpdf")->create('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        //initialisation des parametres du document pdf
        $this->pdf->setPrintHeader(true); //header
        //$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));//herder font par defaut
        $this->pdf->setHeaderFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));//herder font
        //$this->pdf->setPrintFooter(true); //Footer
        $this->pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA)); //Footerfont
        //$this->pdf->SetMargins($this->margeGauche,$this->margeHaute,$this->margeDroite, true); //marges de la page
        $this->pdf->SetMargins($this->margeGauche,$this->margeHaute,$this->margeDroite, 0); //marges de la page
        $this->pdf->SetAutoPageBreak(true,0); //definitely remove margin from bottom
        $this->pdf->setPrintFooter(false);

        //SetMargins($left,$top,$right = -1,$keepmargins = false)
        //$keepmargins(boolean) if true overwrites the default page margins.
        //$this->pdf->SetFooterMargin(4);
        $this->pdf->SetHeaderMargin($this->margeHaute-6);
        $this->pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED); //Police par défaut pour le document

        //pour chaque date de la période
        $numPage = 1;
        $french_days = array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche');
        while($this->nbDePages>=$numPage){
            $aDataPlanningParAvion = array(); //contiendra la génération du tableau de data utilisé par le render Html
            //est-ce que le jour à imprimer fait parti des jours de la semaine selectionné
            $dayofweek = intval($dateDuJour->format('w')-1); //0->lundi, 1 ->mardi, 2->mercredi, 3->jeudi, 4->vebdredi, 5->samedi, 6->dimanche
            if($dayofweek<0){
                $dayofweek = 6;
            }
            if($this->aJoursDeLaSemaine[$dayofweek] != "-") {//si le jour courant est un jour de la semaine voulu


                //si il n'y a pas de mentionSaisonEtSousSaison définie(1er passage) ou si la date du jour courant est hors de la derniére période sous-saison récupérée
                if ((!isset($this->mentionSaisonEtSousSaison)) || ((intval(date_diff($this->debutPeriodeSousSaison, $dateDuJour)->format('%R%a')) < 0) || (intval(date_diff($dateDuJour, $this->finPeriodeSousSaison)->format('%R%a')) < 0))) {

                    //on recherche la nouvellesaison/sous-saison valide de ce jour$this->finPeriodeSousSaison
                    $saisons = $this->em->getRepository('AirCorsicaXKPlanBundle:Saison')->findAll();
                    foreach ($saisons as $uneSaison) {

                        $periodesdelasaison = $uneSaison->getPeriodesSaison();

                        if (sizeof($periodesdelasaison) != 0) { //on compare uniquement les saisons avec une/des périodes saisons

                            foreach ($periodesdelasaison as $unePeriode) {

                                //on a trouver la période de ce jour
                                if ((intval(date_diff($unePeriode->getDateDebut(), $dateDuJour)->format('%R%a')) >= 0) && (intval(date_diff($dateDuJour, $unePeriode->getDateFin())->format('%R%a')) >= 0)) {
                                    //on fais une maj de la mentions saison et sous-saison
                                    $this->mentionSaisonEtSousSaison = /*$uneSaison->getNom()." / ".*/
                                        $unePeriode->getNom();
                                    $this->debutPeriodeSousSaison = $unePeriode->getDateDebut();
                                    $this->finPeriodeSousSaison = $unePeriode->getDateFin();
                                    break 2;  /* Termine les 2 foreach. */
                                }
                            }
                        }
                    }

                }

                //$titre_du_header = ' AIRCORSICA Planning de la journée du ' . $dateDuJour->format('d/m/Y') . ' semaine n°'.$dateDuJour->format('W').' (saison ' . $this->mentionSaisonEtSousSaison . ') - ' . $this->mentionHoraires . '. Document imprimé le ' . $this->dateImpression . ' à '.$this->heureImpression.'.';
                $titre_du_header ='';
                $espacement = '                                                                           ';
                if($this->enteteCompagnie == true){$titre_du_header.=' AIRCORSICA';}else{$espacement.='                        ';}
                $titre_du_header.=' Journée du ';
                if($this->enteteJourSemaine == true){$titre_du_header.=$french_days[$dayofweek].' ';}else{$espacement.='                ';}
                $titre_du_header.=$dateDuJour->format('d/m/Y').' ';
                if($this->enteteNumeroSemaine == true){$titre_du_header.='semaine n° '.$dateDuJour->format('W').' ';}else{$espacement.='                         ';}
                if($this->enteteSaison == true){$titre_du_header.='(saison '.$this->mentionSaisonEtSousSaison.')';}else{$espacement.='                                               ';}
                $titre_du_header.=$espacement;

                if($this->enteteTypeHoraire == true){

                    if( ($this->enteteDateImpression == true) && ($this->enteteNumeroDePage == true) ) {
                        $titre_du_header .= 'Horaires UTC. ';
                    }else if( ($this->enteteDateImpression == true) && ($this->enteteNumeroDePage == false) ) {
                        $titre_du_header .= '                          Horaires UTC.';
                    }else if( ($this->enteteDateImpression == false) && ($this->enteteNumeroDePage == true) ) {
                        $titre_du_header .= '                                                            Horaires UTC. ';
                    }else if( ($this->enteteDateImpression == false) && ($this->enteteNumeroDePage == false) ){
                        $titre_du_header .= '                                                                                 Horaires UTC.';
                    }

                }else{

                    if( ($this->enteteDateImpression == true) && ($this->enteteNumeroDePage == true) ) {
                        $titre_du_header.='                          ';
                    }else if( ($this->enteteDateImpression == true) && ($this->enteteNumeroDePage == false) ) {
                        $titre_du_header.='                                                 ';
                    }else if( ($this->enteteDateImpression == false) && ($this->enteteNumeroDePage == true) ) {
                        $titre_du_header.='                                                                                     ';
                    }

                }

                if($this->enteteDateImpression == true){$titre_du_header .= 'Imprimé le '.$this->dateImpression.' à '.$this->heureImpression.' ';}
                if($this->enteteNumeroDePage == true){$titre_du_header.='Page '.$numPage.'/'.$this->nbDePages;}
                //if($this->enteteNumeroDePage == true){$titre_du_header.='Page '.sprintf("%'03d", $numPage).'/'.sprintf("%'03d", $this->nbDePages);}

                //En-tête de la page courante du document pdf
                $this->pdf->SetHeaderData('', 0, $titre_du_header, '', array(0, 0, 0), array(255, 255, 255)); //array(255,255,255) definis la couleur de la ligne du bas du header à blanc pour ne pas l'imprimer

                //Pied-de-page de ka page courante du document pdf
                $this->pdf->setFooterData(array(0, 0, 0), array(255, 255, 255));
                //$this->pdf->setFooterData(array(0, 0, 0), array(255, 255, 255), 'Page ' . $numPage . '/' . $this->nbDePages, '');//array(255,255,255) definis la couleur de la ligne du haut du footer à blanc pour ne pas l'imprimer

                //récupération des avions immobilisés ce jour
                $aAvionsImmobilisesCeJour = $this->em->getRepository('AirCorsicaXKPlanBundle:Avion')->getAvionsImmobilisesForDate($dateDuJour,$template);

                //récupération des avions avec ceux de aircorsica en premier (à l'identique du planning vol)
                //on cherche dabords les avions AIRCORSICA
                $compagnie = $this->em->getRepository('AirCorsicaXKPlanBundle:Compagnie')->find('1'); //AIR CORSICA à pour id 1 dans la BDB XKPlan
                $criteria = array('compagnie' => $compagnie);
                $order = array('ordre' => 'ASC');
                $aAvion = $this->em->getRepository('AirCorsicaXKPlanBundle:Avion')->findBy($criteria, $order);
                foreach ($aAvion as $unAvion) {

                    $immobilisationEnCours = false;
                    foreach ($aAvionsImmobilisesCeJour as $unAvionImmobilise) {
                        $aPeriodeImmobilisationDeCetAvion = $this->em->getRepository('AirCorsicaXKPlanBundle:PeriodeImmobilisation')->getImmoForAvionAndTemplate($unAvionImmobilise,$template);
                        if ($unAvionImmobilise->getId() == $unAvion->getId()) { // l'avion courant est immobilisé
                            foreach ($aPeriodeImmobilisationDeCetAvion as $unePeriodeDImmobilisation) {
                                if ($unePeriodeDImmobilisation->getDateFin()->format('Y') >= $dateDuJour->format('Y')) {
                                    $immobilisationEnCours = true;
                                    break 1;
                                }
                            }
                        }
                    }

                    array_push($aDataPlanningParAvion, array(
                        "avion" => array(
                            "id" => $unAvion->getId(),
                            "nom" => $unAvion->getNom(),
                            "type" => $unAvion->getTypeAvion()->getCodeIATA(),
                            "estImmobilise" => $immobilisationEnCours,
                            "codeCompagnie" => $unAvion->getCompagnie()->getCodeIATA()
                        ),
                        "vols" => array()
                    ));
                }
                //on rajoute les autres avions
                $aAvion = $this->em->getRepository('AirCorsicaXKPlanBundle:Avion')->findNotCompagnie('1');
                foreach ($aAvion as $unAvion) {

                    $immobilisationEnCours = false;
                    foreach ($aAvionsImmobilisesCeJour as $unAvionImmobilise) {
                        $aPeriodeImmobilisationDeCetAvion = $this->em->getRepository('AirCorsicaXKPlanBundle:PeriodeImmobilisation')->getImmoForAvionAndTemplate($unAvionImmobilise,$template);
                        if ($unAvionImmobilise->getId() == $unAvion->getId()) { // l'avion courant est immobilisé
                            foreach ($aPeriodeImmobilisationDeCetAvion as $unePeriodeDImmobilisation) {
                                if ($unePeriodeDImmobilisation->getDateFin()->format('Y') >= $dateDuJour->format('Y')) {
                                    $immobilisationEnCours = true;
                                    break 1;
                                }
                            }
                        }
                    }

                    array_push($aDataPlanningParAvion, array(
                        "avion" => array(
                            "id" => $unAvion->getId(),
                            "nom" => $unAvion->getNom(),
                            "type" => $unAvion->getTypeAvion()->getCodeIATA(),
                            "estImmobilise" => $immobilisationEnCours,
                            "codeCompagnie" => $unAvion->getCompagnie()->getCodeIATA()
                        ),
                        "vols" => array()
                    ));
                }

                //récupération des vols
                $jourCourantPlusUn->setTimestamp($dateDuJour->getTimestamp());
                $jourCourantPlusUn->modify('+1 day');
                $aheureMini = explode(":", "23:59:59");
                $aheureMaxi = explode(":", "00:00:01");
                $repoTemplate = $this->em->getRepository('AirCorsicaXKPlanBundle:Template');
                $templateCurrent = $repoTemplate->find($this->templateId);
                $aVols = $this->em->getRepository('AirCorsicaXKPlanBundle:Vol')->getVolsForDate($templateCurrent, $dateDuJour);

                //on verifier qu'il n'y ai pas un vol de la veille qui arrive ce jour
                $aVolsDepartJoursPrecedentAtterissageCeJour = $this->em->getRepository('AirCorsicaXKPlanBundle:Vol')->getDayBeforeVolsArrivingOnDate($templateCurrent,$dateDuJour);
                //si oui on le rajoute au tableau de vols
                if(count($aVolsDepartJoursPrecedentAtterissageCeJour)!=0){
                    foreach($aVolsDepartJoursPrecedentAtterissageCeJour as $unVolPartantLaVeille){
                        array_push($aVols,$unVolPartantLaVeille);
                    }
                }

                foreach ($aVols as $unVol) {

                    $dateFinPeriodeVol = $unVol->getPeriodeDeVol()->getDateFin();
                    $ajourDeValidite = array_diff($unVol->getPeriodeDeVol()->getJoursDeValidite(), array('-')); //on enléve les - de l'array, il ne reste que les id des jours valide

                    //if (in_array(strval(date('N', $dateDuJour->getTimestamp())), $ajourDeValidite)) {// le vol est effectué le jour de la semaine du jour demandé
                    if( (in_array(strval(date('N', $dateDuJour->getTimestamp())),$ajourDeValidite)) || (intVal($unVol->getPeriodeDeVol()->getDecollage()->format('Hi'))>intVal($unVol->getPeriodeDeVol()->getAtterissage()->format('Hi'))) ){//si le vol est effectué le jour de la semaine du jour demandé ou bien si l'heure de départ plus grande que heure d'arrivé (vol partant la veille)

                        //if ((($unVol->getPeriodeDeVol()->getDecollage()->getTimestamp() > $unVol->getPeriodeDeVol()->getAtterissage()->getTimestamp()) && (in_array(strval(date('N', $jourCourantPlusUn->getTimestamp())), $ajourDeValidite)) && ($jourCourantPlusUn->getTimestamp() <= $dateFinPeriodeVol->getTimestamp())) || ($unVol->getPeriodeDeVol()->getDecollage()->getTimestamp() < $unVol->getPeriodeDeVol()->getAtterissage()->getTimestamp())) {
                        if( ( ($unVol->getPeriodeDeVol()->getDecollage()->getTimestamp()>$unVol->getPeriodeDeVol()->getAtterissage()->getTimestamp()) /*&& (in_array(strval(date('N', $jourCalendrierPlusUn->getTimestamp())),$ajourDeValidite)) && ($jourCalendrierPlusUn->getTimestamp()<=$dateFinPeriodeVol->getTimestamp())*/ ) || ($unVol->getPeriodeDeVol()->getDecollage()->getTimestamp()<$unVol->getPeriodeDeVol()->getAtterissage()->getTimestamp()) ){
                            //si le timestamp de l'heure de décolage est supérieur au timestamp de l'heure d'attérissage (le vol arrive le lendemain) ET SI le jour suivant fait partie des jour ou il y a le vol (genre le mercredi jour suivant du mardi) ET SI le jour suivant est bien inférieur ou égale à la fin de péiode de ce vol
                            //ou bien si le timestamp de l'heure de décolage est inférieur au timestamp de l'heure d'attérissage (cas générale)

                            //recherche heure minimum du tableau en fonction des vol de ce jour
                            $adecollageMini = explode(":", $unVol->getPeriodeDeVol()->getDecollage()->format('H:i:s'));
                            if ($adecollageMini[0] < $aheureMini[0]) {

                                $aheureMini[0] = $adecollageMini[0];
//                                $aheureMini[1] = $adecollageMini[1];
//                                $aheureMini[2] = $adecollageMini[2];

                            }
//                            else if ($adecollageMini[0] == $aheureMini[0]) {
//
//                                if ($adecollageMini[1] < $aheureMini[1]) {
//
//                                    $aheureMini[1] = $adecollageMini[1];
//                                    $aheureMini[2] = $adecollageMini[2];
//
//                                } else if ($adecollageMini[1] == $aheureMini[1]) {
//
//                                    if ($adecollageMini[2] < $aheureMini[2]) {
//
//                                        $aheureMini[2] = $adecollageMini[2];
//
//                                    }
//                                }
//                            }

                            //recherche heure maximum du tableau en fonction des vol de ce jour
                            $aatterissageMaxi = explode(":", $unVol->getPeriodeDeVol()->getAtterissage()->format('H:i:s'));
                            if ($aatterissageMaxi[0] > $aheureMaxi[0]) {

                                if (($aatterissageMaxi[1] == "00") && ($aatterissageMaxi[2] == "00")) {
                                    $aatterissageMaxi[0] = sprintf("%02d", (intval($aatterissageMaxi[0]) - 1));
                                }

                                $aheureMaxi[0] = $aatterissageMaxi[0];
                                $aheureMaxi[1] = $aatterissageMaxi[1];
                                $aheureMaxi[2] = $aatterissageMaxi[2];


                            } else if ($aatterissageMaxi[0] == $aheureMaxi[0]) {

                                if ($aatterissageMaxi[1] > $aheureMaxi[1]) {

                                    $aheureMaxi[1] = $aatterissageMaxi[1];
                                    $aheureMaxi[2] = $aatterissageMaxi[2];

                                } else if ($aatterissageMaxi[1] == $aheureMaxi[1]) {

                                    if ($aatterissageMaxi[2] > $aheureMaxi[2]) {

                                        $aheureMaxi[2] = $aatterissageMaxi[2];

                                    }
                                }
                            } else if ( (intval($aatterissageMaxi[0]) < 4 ) && (intval($aheureMaxi[0]) < intval($adecollageMini[0])) ){

                                $aheureMaxi[0] = $adecollageMini[0];
                                $aheureMaxi[1] = $adecollageMini[1];
                                $aheureMaxi[2] = $adecollageMini[2];

                            }

                            //mise à jour du tableau $aDataPlanningParAvion
                            $aheureMaxiTempChevJrSuiv = 23;
                            foreach ($aDataPlanningParAvion as $key => $unPlanningAvion) {
                                $toto = $unVol->getNumero();
                                if ($unPlanningAvion["avion"]["id"] == $unVol->getAvion()->getId()) {
                                    // vol qui chevauche 2 jours
                                    if(intVal($unVol->getPeriodeDeVol()->getDecollage()->format('Hi'))>intVal($unVol->getPeriodeDeVol()->getAtterissage()->format('Hi'))) {

                                        if (intval(date_diff($unVol->getPeriodeDeVol()->getDateDebut(), $dateDuJour)->format('%R%a')) == 0) {//vol qui part ce jour et arrive le lendemain

                                            array_push($aDataPlanningParAvion[$key]["vols"], array(
                                                "id" => $unVol->getId(),
                                                "villeDepart" => $unVol->getLigne()->getAeroportDepart()->getCodeIATA(),
                                                "heureDepart" => $unVol->getPeriodeDeVol()->getDecollage()->format('H:i:s'),
                                                "codeVol" => $unVol->getNumero(),
                                                "villeArrivee" => $unVol->getLigne()->getAeroportArrivee()->getCodeIATA(),
                                                "heureArrivee" => $unVol->getPeriodeDeVol()->getAtterissage()->format('H:i:s'),
                                                "typeDeVol" => $unVol->getTypeDeVol()->getCodeType(),
                                                "backgroundColor" => $unVol->getTypeDeVol()->getCodeCouleur(),
                                                "chevauchejour" => true,
                                                "departlaveille" => false
                                            ));

                                            //heure maximum
                                            if($this->affichagetranchehorairejoursuivant == false) {
                                                $aheureMaxi[0] = "23";
                                            }else{
                                                $attTemp = 23 + intVal( $unVol->getPeriodeDeVol()->getAtterissage()->format('H') )+1; //atterissage 0h40 donne 24h40 par exemple
                                                if($aheureMaxiTempChevJrSuiv<$attTemp){
                                                    $aheureMaxiTempChevJrSuiv = $attTemp;
                                                }

                                            }
                                            $aheureMaxi[1] = "59";
                                            $aheureMaxi[2] = "59";

                                        }else{//vol qui part la veille et arrive ce jour

                                            array_push($aDataPlanningParAvion[$key]["vols"], array(
                                                "id" => $unVol->getId(),
                                                "villeDepart" => $unVol->getLigne()->getAeroportDepart()->getCodeIATA(),
                                                "heureDepart" => $unVol->getPeriodeDeVol()->getDecollage()->format('H:i:s'),
                                                "codeVol" => $unVol->getNumero(),
                                                "villeArrivee" => $unVol->getLigne()->getAeroportArrivee()->getCodeIATA(),
                                                "heureArrivee" => $unVol->getPeriodeDeVol()->getAtterissage()->format('H:i:s'),
                                                "typeDeVol" => $unVol->getTypeDeVol()->getCodeType(),
                                                "backgroundColor" => $unVol->getTypeDeVol()->getCodeCouleur(),
                                                "chevauchejour" => true,
                                                "departlaveille" => true
                                            ));

                                            $aheureMini[0] = "00";
                                        }
                                    }else{

                                        array_push($aDataPlanningParAvion[$key]["vols"], array(
                                            "id" => $unVol->getId(),
                                            "villeDepart" => $unVol->getLigne()->getAeroportDepart()->getCodeIATA(),
                                            "heureDepart" => $unVol->getPeriodeDeVol()->getDecollage()->format('H:i:s'),
                                            "codeVol" => $unVol->getNumero(),
                                            "villeArrivee" => $unVol->getLigne()->getAeroportArrivee()->getCodeIATA(),
                                            "heureArrivee" => $unVol->getPeriodeDeVol()->getAtterissage()->format('H:i:s'),
                                            "typeDeVol" => $unVol->getTypeDeVol()->getCodeType(),
                                            "backgroundColor" => $unVol->getTypeDeVol()->getCodeCouleur(),
                                            "chevauchejour" => false
                                        ));

                                    }

                                    //la tranche horaire maxi des vol qui chevauchen le jours suivant
                                    if($aheureMaxiTempChevJrSuiv>23){
                                        $aheureMaxi[0] = $aheureMaxiTempChevJrSuiv;
                                    }

                                    break 1;
                                }
                            }

                            //on trie les vols par heure de depart
                            foreach ($aDataPlanningParAvion as $key => $unPlanningAvion) {
                                if ($unPlanningAvion["avion"]["id"] == $unVol->getAvion()->getId()) {
                                    //$this->array_sort_by_column($aDataPlanningParAvion[$key]["vols"], 'heureDepart');
                                    $leVolQuiChevaucheJour = array();
                                    foreach($aDataPlanningParAvion[$key]["vols"] as $keyvol => $unVolDuPlanningAvion){
                                        if(($unVolDuPlanningAvion['chevauchejour'] == true) &&($unVolDuPlanningAvion['departlaveille'] == true)){
                                            $leVolQuiChevaucheJour = $aDataPlanningParAvion[$key]["vols"][$keyvol];
                                            unset($aDataPlanningParAvion[$key]["vols"][$keyvol]);
                                        }
                                    }
                                    $this->array_sort_by_column($aDataPlanningParAvion[$key]["vols"], 'heureDepart');
                                    if(count($leVolQuiChevaucheJour)!=0){
                                        array_unshift($aDataPlanningParAvion[$key]["vols"],$leVolQuiChevaucheJour);
                                    }
                                }
                            }

                        }

                    }

                }

                //restriction des résultat en fonction des option d'impression choisie
                if($this->optionImpressionAvions == 1) {
                    //on restreind l'échantillon aux avions ayant des vols ce jour là ou immobilisé
                    foreach ($aDataPlanningParAvion as $key => $value) {
                        if($value["avion"]["estImmobilise"] == false) {
                            if (count($value["vols"]) == 0) {
                                unset($aDataPlanningParAvion[$key]);
                            }
                        }
                    }
                    //reindexation du tableau
                    $aDataPlanningParAvion = array_values($aDataPlanningParAvion);
                }elseif($this->optionImpressionAvions == 2) {
                    //on restreind l'échantillon aux avions ayant des vols ce jour là
                    foreach ($aDataPlanningParAvion as $key => $value) {
                        if (count($value["vols"]) == 0) {
                            unset($aDataPlanningParAvion[$key]);
                        }
                    }
                    //reindexation du tableau
                    $aDataPlanningParAvion = array_values($aDataPlanningParAvion);
                }elseif($this->optionImpressionAvions == 3) {
                    //on restreind l'échantillons au vols selectionnés
                    foreach ($aDataPlanningParAvion as $key => $value) {
                        if(!in_array($value["avion"]["id"],$this->aIdAvionsAImprimer)){//si ceta avions ne fait pas partis de ceux à impripmer
                            unset($aDataPlanningParAvion[$key]);
                        }
                    }
                    //reindexation du tableau
                    $aDataPlanningParAvion = array_values($aDataPlanningParAvion);
                }

                //si ont choisi de ne doit pas adapter la legende des tranches horaires
                if($this->autoResizeTranchesHoraires == false){

                    //Heure minimum
                    //si le début de la tranche horaires est normal
                    if(intval($aheureMini[0]) >= 4){
                        $aheureMini[0] ="04";
                        $aheureMini[1] ="00";
                        $aheureMini[2] ="00";
                    }else{ //tranche horaire à partir de minuit
                        $aheureMini[0] ="00";
                        $aheureMini[1] ="00";
                        $aheureMini[2] ="01";
                    }
                    //heure maximum
                    $aheureMaxi[0] = "23";
                    $aheureMaxi[1] = "59";
                    $aheureMaxi[2] = "59";
                }


                //creation du planning vol html de ce jour (en fonction de heure mini, heure maxi)
                $html = $this->htmlRenderOneDayPlanning($aDataPlanningParAvion, $aheureMini, $aheureMaxi);

                //Exemple code de damien dans vol controller
                //$pdf->SetAuthor('Air Corsica');
                //$pdf->SetTitle(('XKPLAN'));
                //$pdf->SetSubject('Time Table');
                //$pdf->setFontSubsetting(true);
                //$pdf->setPrintHeader(true);
                //$pdf->setPrintFooter(false);
                //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                //$pdf->SetFont('helvetica', '', 11, '', true);
                //$pdf->SetMargins(10, 20, 10, true);


                //ajout d'une page au document pdf
                $this->pdf->AddPage();

                //injection de ce html dans la page courante de ce pdf
                $this->pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);

                //on re-initialise le template header pour pouvoir le changer
                $this->pdf->resetHeaderTemplate();


            }

            //on passe au jour suivant(test de sortie de la boucle while)
            $dateDuJour->modify('+1 day');
            $dateDuJour->setTime(0,0,0);

//            if($impressionUneJournee == true){
//                break 1;
//            }

            //on incrémente le numéro de la page
            $numPage++;

        }

        //on retourne le pdf généré
        //$this->pdf->lastPage();
        //ob_clean();
        $response = new Response($this->pdf->Output('AirCorsica.pdf'));
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;




    }


}
