<?php
/**
 * Created by PhpStorm.
 * User: damie
 * Date: 14/03/2017
 * Time: 17:04
 */

namespace AirCorsica\XKPlanBundle\Entity\Message;


use AirCorsica\XKPlanBundle\Entity\CodeShareVol;
use AirCorsica\XKPlanBundle\Entity\Ligne;
use AirCorsica\XKPlanBundle\Entity\Periode;
use AirCorsica\XKPlanBundle\Entity\PeriodeDeVol;
use AirCorsica\XKPlanBundle\Entity\Saison;
use AirCorsica\XKPlanBundle\Entity\Vol;
use AirCorsica\XKPlanBundle\Repository\VolRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use League\Period\Period;

class SSIM extends AbstractMessage implements InterfaceMessage
{
    const type1 = "1AIRLINE STANDARD SCHEDULE DATA SET";
    const compagnie = "XK";

    protected $aVols;

    public function __construct($aVols)
    {
        $aVolSNonDeleteste = array();
        /** @var Vol $vol */
        foreach ($aVols as $vol){
            if($vol->getPeriodeDeVol()->getDeleste()
                || "pendingCancel" == $vol->getPeriodeDeVol()->getEtat()
                || "cancel" == $vol->getPeriodeDeVol()->getEtat() ){
                continue;
            }
            $aVolSNonDeleteste[] = $vol;
        }

        $this->aVols = $aVolSNonDeleteste;
    }

    /**
     * @return mixed
     */
    public function getAVols()
    {
        return $this->aVols;
    }

    /**
     * Analyser le vol et en déduire les lots de messages SCR à générer et à distribuer
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Vol $vol
     * @return boolean
     */
    public function necessitemessages(\AirCorsica\XKPlanBundle\Entity\Vol $vol)
    {
        // TODO: Implement necessitemessages() method.
    }

    /**
     * Génére un(des) message(s)
     *
     * @param array $datamessagetogenerate
     * @return array | boolean
     */
    public function generer(array $datamessagetogenerate)
    {

    }

    /**
     * Génére un(des) message(s)
     *
     * @param array $datamessagetogenerate
     * @return array | boolean
     */
    public function genererMessage($em, $debutPeriodeDiffusee, $finPeriodeDiffusee)
    {
        //Uniquement pour ce script
        ini_set('max_execution_time', 0);

        $return = "";
        $return = $this->addType1($return);

        $resSaisonIATA = $this->getSaisonIATA($em,$debutPeriodeDiffusee, $finPeriodeDiffusee);

        if(sizeof($resSaisonIATA) == 1){
            $saison = $resSaisonIATA[0];
        }else{
            $saison = new Saison();
            $periode = new Periode($debutPeriodeDiffusee,$finPeriodeDiffusee);
            $saison->setPeriode($periode);
        }

        $return = $this->addType2($return,$debutPeriodeDiffusee, $finPeriodeDiffusee,$saison);

        $record = 2;

        $aVolDeTest = array();
        /** @var VolRepository $repo */
        $repo = $em->getRepository('AirCorsicaXKPlanBundle:Vol');

        $aVolUnitaire = array();
        $aVolUnitairePrecedent = array();
        $add = new \DateInterval("P1D");
        $sommeType3Type4 = 0;
        /** @var Vol $vol */
        $numeroVolPrecedent = null;
        $sequence = 1;
        foreach ($this->aVols as $vol){
            $aVolUnitaire = array();

            $dateMouvanteSuivante = null;
            $volUnitairePrecedent = array();
            $periodeReference = $vol->getPeriodeDeVol();

            $dateDebut = clone $periodeReference->getDateDebut();
            $dateFin = clone $periodeReference->getDateFin();
            $res = $repo->getVolsAvecMemeAvion($vol, $vol->getTemplate(),$saison);
            if(!sizeof($res)){
                $dateMaxPeriode = $dateFin;
            }else {
                /** @var Vol $lastVol */
                $lastVol = end($res);

                $dateMaxPeriode = ($lastVol->getPeriodeDeVol()->getDateFin() > $dateFin) ? $dateFin : clone $lastVol->getPeriodeDeVol()->getDateFin();
            }

            $i=0;
            while ($dateDebut<=$dateMaxPeriode){

                $dateMouvante = clone $dateDebut;

                $heureMaxArriveeInferieure = 0;

                /** @var Vol $volCourant */
                $aVolUnitaire[$i]['numero']= $vol->getNumero(true);
                $aVolUnitaire[$i]['jour'] = $dateMouvante;
                $aVolUnitaire[$i]['jourCourant'] = $dateMouvante->format('N');
                $aVolUnitaire[$i]['numeroSuivant'] = null;
                $aVolUnitaire[$i]['volSuivant'] = null;
                foreach ($res as $volCourant)
                {
                    /** @var PeriodeDeVol $periodeCourante */
                    $periodeCourante = $volCourant->getPeriodeDeVol();

                    if($periodeCourante->getDateFin() >= $dateDebut && $periodeCourante->getDateDebut() <= $dateDebut
                        && in_array($dateDebut->format('N'),$periodeCourante->getJoursDeValidite())){
                        $aVolUnitaire[$i]['periode'][] = $periodeCourante;

                        if(in_array($dateDebut->format('N'),$periodeReference->getJoursDeValidite()) && $periodeReference->getDateDebut() <= $dateDebut){
                            if( $periodeCourante->getDecollage() > $periodeReference->getAtterissage()){
                                if($heureMaxArriveeInferieure > $periodeCourante->getDecollage()->format("Hi") || $heureMaxArriveeInferieure == 0){
                                    $heureMaxArriveeInferieure = $periodeCourante->getDecollage()->format("Hi");
                                    $numeroVolSuivant = $periodeCourante->getVol()->getNumero(true);
                                    $aVolUnitaire[$i]['numeroSuivant'] = $numeroVolSuivant;
                                    $aVolUnitaire[$i]['volSuivant'] = $periodeCourante->getVol();
                                }
                            }

                        }
                    }
                }

                if (in_array($aVolUnitaire[$i]['jourCourant'],$periodeReference->getJoursDeValidite())) {
                    $volUnitairePrecedent = $aVolUnitaire[$i];
                    $finParcoursSuivant = ($aVolUnitaire[$i]['numeroSuivant'] != null) || ($dateMouvanteSuivante > $dateMaxPeriode);
                }else{
                    if(isset($volUnitairePrecedent['numeroSuivant'])) {
                        $aVolUnitaire[$i]['numeroSuivant'] = $volUnitairePrecedent['numeroSuivant'];
                        $aVolUnitaire[$i]['volSuivant'] = $periodeCourante->getVol();
                    }else{
                        $aVolUnitaire[$i]['numeroSuivant'] = null;
                        $aVolUnitaire[$i]['volSuivant'] = null;
                    }
                    $finParcoursSuivant = true;
                }

                if(!$finParcoursSuivant) {

                    $dateMouvanteSuivante = clone $dateDebut;
                    $dateMouvanteSuivante->add($add);
                    $jourLePlusProche = null;
                    $finParcoursSuivant = false;

                    while (!$finParcoursSuivant) {
                        $nJour = $dateMouvanteSuivante->format("N");

                        foreach ($res as $key=>$volCourant) {
                            /** @var PeriodeDeVol $periodeCourante */
                            $periodeCourante = $volCourant->getPeriodeDeVol();

                            if ($periodeCourante->getDateFin() >= $dateMouvanteSuivante && $periodeCourante->getDateDebut() <= $dateMouvanteSuivante
                                && in_array($nJour, $periodeCourante->getJoursDeValidite())
                            ) {

                                $aVolUnitaire[$i]['periode'][] = $periodeCourante;

                                if ($jourLePlusProche <= $dateMouvanteSuivante) {
                                    $jourLePlusProche = clone $dateMouvanteSuivante;
                                    if ($heureMaxArriveeInferieure > $periodeCourante->getDecollage()->format("Hi") || $heureMaxArriveeInferieure == 0) {
                                        $heureMaxArriveeInferieure = $periodeCourante->getDecollage()->format("Hi");
                                        $numeroVolSuivant = $periodeCourante->getVol()->getNumero(true);
                                        $aVolUnitaire[$i]['numeroSuivant'] = $numeroVolSuivant;
                                        $aVolUnitaire[$i]['volSuivant'] = $periodeCourante->getVol();
                                        $aVolUnitaire[$i]['jourLePlusProche'] = $jourLePlusProche;
                                        /**
                                         * @todo calculer durée entre le vol courant et le prochain vol
                                         */
                                        //Type 3 Aircraft Rotation Layover caractère 145-145, conditionnel
                                        $h = null;

                                        if($aVolUnitaire[$i]['volSuivant'] !== null){
                                            $datePourCalcul = clone $jourLePlusProche;
                                            $datePourCalcul->setTime($periodeCourante->getVol()->getPeriodeDeVol()->getAtterissage()->format('H'), $periodeCourante->getVol()->getPeriodeDeVol()->getAtterissage()->format('i'));
                                            $jourReference = clone $aVolUnitaire[$i]['jour'];
                                            $jourReference->setTime($vol->getPeriodeDeVol()->getAtterissage()->format('H'),$vol->getPeriodeDeVol()->getAtterissage()->format('i'));
                                            $debut = $jourReference->format('U');
                                            $fin = $datePourCalcul->format('U');
                                            $h = ($fin - $debut) / 3600;
                                        }

                                        if($h !== null){
                                            switch ($h){
                                                case $h < 24 :
                                                    $aVolUnitaire[$i]['differenceEntreVol'] = " ";
                                                    break;
                                                case $h >= 24 && $h < 48 :
                                                    $aVolUnitaire[$i]['differenceEntreVol'] = "1";
                                                    break;
                                                case $h >= 48 && $h < 72 :
                                                    $aVolUnitaire[$i]['differenceEntreVol'] = "2";
                                                    break;
                                                case $h >= 72 && $h < 96 :
                                                    $aVolUnitaire[$i]['differenceEntreVol'] = "3";
                                                    break;
                                                case $h >= 96 && $h < 120 :
                                                    $aVolUnitaire[$i]['differenceEntreVol'] = "4";
                                                    break;
                                                case $h >= 120 && $h < 144 :
                                                    $aVolUnitaire[$i]['differenceEntreVol'] = "5";
                                                    break;
                                                case $h >= 144 && $h < 168 :
                                                    $aVolUnitaire[$i]['differenceEntreVol'] = "6";
                                                    break;
                                                case $h >= 168 && $h < 192 :
                                                    $aVolUnitaire[$i]['differenceEntreVol'] = "7";
                                                    break;
                                                case $h >= 192 && $h < 216 :
                                                    $aVolUnitaire[$i]['differenceEntreVol'] = "8";
                                                break;
                                                case $h >= 216 && $h < 240 :
                                                    $aVolUnitaire[$i]['differenceEntreVol'] = "9";
                                                    break;
                                                default :
                                                    $aVolUnitaire[$i]['differenceEntreVol'] = " ";
                                                    break;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        $dateMouvanteSuivante = $dateMouvanteSuivante->add($add);
                        $finParcoursSuivant = ($aVolUnitaire[$i]['numeroSuivant'] != null) || ($dateMouvanteSuivante > $dateMaxPeriode);
                    }
                }
                $dateDebut->add($add);
                $i++;
            }

            $message = array();
            $aVolUnitaire = array_reverse($aVolUnitaire);
            $index=0;
            foreach ($aVolUnitaire as $volUnitaire){
                if($volUnitaire['jour'] <= $periodeReference->getDateFin() && $volUnitaire['jour'] >= $periodeReference->getDateDebut()){
                    //If existe
                    if(sizeof($message)){
                        $existe = -1;
                        foreach ($message as $key=>$m){
                            $testDateDebut = clone $m['dateDebut'];
                            $testDateFin = clone $m['dateFin'];
                            if($m["numero"]==$volUnitaire['numero'] && $testDateDebut->sub($add)<=$volUnitaire['jour']
                                && $testDateFin->add($add)>=$volUnitaire['jour']){
                                if($m["numeroSuivant"] == $volUnitaire["numeroSuivant"] || $volUnitaire["numeroSuivant"] == null){
                                    $existe = $key;
                                }
                            }
                        }
                    }else{
                        $existe = -1;
                    }
                    $jourValidite = $volUnitaire['jour']->format("N");
                    if($existe<0){
                        $message[$index]['dateDebut'] = clone $volUnitaire['jour'];
                        $message[$index]['dateFin'] = clone $volUnitaire['jour'];
                        $aJourValidite = array("-","-","-","-","-","-","-");
                        if(in_array($jourValidite,$periodeReference->getJoursDeValidite())){
                            $aJourValidite[$jourValidite-1] = $jourValidite;
                        }
                        $message[$index]['jourValidite'] = $aJourValidite;
                        $message[$index]['numero'] = $volUnitaire['numero'];
                        $message[$index]['numeroSuivant'] = $volUnitaire['numeroSuivant'];
                        $message[$index]['vol'] = $vol;
                        $message[$index]['volSuivant'] = $volUnitaire['volSuivant'];
                        if(array_key_exists('jourLePlusProche',$volUnitaire)){
                            $message[$index]['jourLePlusProche'] = $volUnitaire['jourLePlusProche'];
                            $message[$index]['differenceEntreVol'] = $volUnitaire['differenceEntreVol'];
                        }
                        $index++;
                    }else{
                        $messageExistant = $message[$existe];

                        $testDateDebut = clone $messageExistant["dateDebut"];
                        $testDateFin = clone $messageExistant["dateFin"];

                        if($volUnitaire['jour'] == $testDateDebut->sub($add)) {
                            $messageExistant["dateDebut"] = $volUnitaire['jour'];
                        }elseif($volUnitaire['jour'] == $testDateFin->add($add)){
                            $messageExistant["dateFin"] = $volUnitaire['jour'];
                            }
                            else{
                                throw new \LogicException('Impossible');
                        }
                        if(in_array($jourValidite,$periodeReference->getJoursDeValidite())){
                            $aJourValidite = $messageExistant['jourValidite'];
                            $aJourValidite[$jourValidite-1] = $jourValidite;
                            $messageExistant['jourValidite'] = $aJourValidite;
                        }
                        $message[$existe] = $messageExistant;
                    }
                }
            }

            foreach ($message as $m){
                $jourValiditeMessage = $m['jourValidite'];
                $testJourValidite = false;
                for($a=0;$a<=6;$a++){
                    if($jourValidite[$a] != "-"){
                        $testJourValidite = true;
                        break;
                    }
                }
                if(!$testJourValidite){
                    continue;
                }
                if($numeroVolPrecedent == $m["numero"]){
                    $sequence += 1;
                } else{
                    $sequence = 1;
                }
                $sommeType3Type4++;
                $return = $this->addType3($m, $return, $sequence, $record=$record+1);
                $sommeType3Type4++;
                $return = $this->addType4($m, $return, $sequence, $record=$record+1);
//                $return = $this->addType4($message, $return, $sequence, $record);
                $numeroVolPrecedent = $m["numero"];
            }

        }
            //Ne marche pas dans rocade
//        $return = $this->addType5($return,$record,$sommeType3Type4);

        //Ne marche pas dans rocade
        //Fin du Fichier 10 lignes de 0
//        for($i=1;$i<=10;$i++) {
//            $return .= $this->genererZero(200) . "\n";
//        }

        return $return;
    }

    /**
     * @param $return
     */
    private function addType1($return){
        //Type 1 en tête caractère de 1-35
        $return .= self::type1;
        //Type 1 5 blancs caratère de 36-40
        $return .= $this->genererBlanc(5);

        //Optionnel
        //Type 1 nombre de saison où blanc si choix de date de début et fin caractère 41
        $return .= $this->genererBlanc(1);

        //Type 1 150 blancs caractère 42-191
        $return .= $this->genererBlanc(150);
        //Type 1 data set serial numbre
        $return .= "001";
//        //Type 1 N° du record
        $return .= str_pad(1,6,0,STR_PAD_LEFT);
        $return .= "\r\n";

        //Type 1 suivi de 4 lignes de 0
        for ($i=1;$i<=4;$i++)
        {
            $return .= $this->genererZero(200)."\r\n";
        }
        return $return;
    }

    /**
     * @param $return
     */
    private function addType2($return, $debutPeriodeDiffusee, $finPeriodeDiffusee, $saison){
        //Type 2 en tête caractère de 1-1
        $return .= 2;
        //Type 2 time mode caratère 2
        $return .= 'U';
        //Type 2 compagnie diffusé cadré à gauche caractère 3-5 => il faut rajouter un blanc car la compagnie est sur 2 caractères
        $return .= self::compagnie;
        $return .= $this->genererBlanc(1);
        //Type 2 1 blanc caractère 6
        $return .= $this->genererBlanc(1);
        //Type 2 caractère 7-10
        $return .= "0008";
        //Optionnel
        //Type 2 Saison S ou W suivi de l'année AA caractère 11-13
        if($saison instanceof Saison){
            if($saison->getId()){
                $return .= $saison->getNom();
            }else{
                $return .= $this->genererBlanc(3);
            }

        }else{
            $return .= $this->genererBlanc(3);
        }

        //Type 2 1 blanc caractere 14
        $return .= $this->genererBlanc(1);
        //Type 2 Période de la saison caractère 15-28
        $dateDebutDiffusion = DateTime::createFromFormat('d-m-Y',$debutPeriodeDiffusee);
        $dateFinDiffusion = DateTime::createFromFormat('d-m-Y',$finPeriodeDiffusee);
        $return .= strtoupper($dateDebutDiffusion->format('dMy').$dateFinDiffusion->format('dMy'));
        //Type 2 Date du jour de génération du SSIM caractère 29-35
        $now = new \DateTime();
        $return .= strtoupper($now->format('dMy'));
        //Optionnel
        //Type 2 Titre des données, des blancs dans l'ancienne version actuelle caractère 36-64
        $return .= $this->genererBlanc(29);
        //Optionnel
        //Type 2 Date de diffusion = date du jour caractère 65-71
        $return .= strtoupper($now->format('dMy'));
        //Type 2 Type de programme caractère 72-72
        $return .= "P";
        //Optionnel
        //Type 2 Creator's reference, des blancs dans l'ancienne version caractère 73-107
        $return .= $this->genererBlanc(35);
        //Conditionnel
        //Type 2 Duplicate airline designator marker,  blanc dans l'ancienne version caractère 108-108
        $return .= $this->genererBlanc(1);
        //Optionnel
        //Type 2 Informations générales, des blancs dans l'ancienne version caractère 109-169
        $return .= $this->genererBlanc(61);
        //Optionnel
        //Type 2 In Flight Service information, des blancs dans l'ancienne version caractère 170-188
        $return .= $this->genererBlanc(19);
        //Optionnel
        //Type 2 Electronic ticketing information caractère 189-190
        $return .= "EN";
        //Type 2 Heure et minute de création caractère 191-194
        $return .= $now->format('Hi');
        //Type 2 record caractère 195-200
        $return .= str_pad(2,6,0,STR_PAD_LEFT);
        $return .= "\r\n";

        //Type 2 suivi de 4 lignes de 0
        for ($i=1;$i<=4;$i++)
        {
            $return .= $this->genererZero(200)."\r\n";
        }
        return $return;
    }

    /**
     * @param $return
     */
    private function addType3($message, $return, $sequence, $record)
    {
        /** @var Vol $vol */
        $vol = $message['vol'];
        /** @var Vol $volSuivant */
        $volSuivant = $message['volSuivant'];
        //Type 3 en tête caractère de 1-1
        $return .= 3;
        //Type 3 suffixe opérationnel, champ conditionnel, peut être un blanc caractère de 2-2
        $return .= $this->genererBlanc(1);
        //Type 3 Compagnie diffusée caractère 3-5) => il faut rajouter un blanc car la compagnie est sur 2 caractères
        $return .= self::compagnie;
        $return .= $this->genererBlanc(1);
        //Type 3 Numéro de Vol caratère 6-9, cadrée à droite
        $return .= str_pad($message["numero"],4," ",STR_PAD_LEFT);
        //Type 3 n° séquence de la période de vol
//        if($sequence>=100){
//            $sequence = 0;
//        }
        $sequence2 = $sequence;
        if($sequence2>=100){
            $test = 1;
        }
        if(strlen($sequence2)>2){
            $sequence2 = substr($sequence2,1,2);
        }
        $return .= str_pad($sequence2,2,0,STR_PAD_LEFT);
        //Type 3 n° de séquence du tronçon caractère 12-13, valeur par défaut
        $return .= "01";
        //Type 3 Type de service IATA, caractère 14-14, valeur par défaut
        $return .= $vol->getTypeDeVol()->getCodeService();
        //Type 3 date de début de la période caractère 15-21
        $return .= strtoupper($message['dateDebut']->format("dMy"));
        //Type 3 date de fin de la période caractère 22-28
        $return .= strtoupper($message['dateFin']->format("dMy"));
        //Type 3 jour de validité caractère 29-35, cadré à gauche
        foreach ($message['jourValidite'] as $j){
            if($j == "-"){
                $return .= $this->genererBlanc(1);
            }else{
                $return .= $j;
            }
        }
//        $return .= implode("",$message['jourValidite']);
        //Type 3 Fréquence caractère 36-36, optionnelle, peut être un blanc
        $return .= $this->genererBlanc(1);
        //Type 3 code aéroport de départ caractère 37-39
        $return .= $vol->getLigne()->getAeroportDepart()->getCodeIATA();
        //Type 3 heure de départ passager caractère 40-43
        $return .= $vol->getPeriodeDeVol()->getDecollage()->format('Hi');
        //Type 3 heure de départ avion caractère 44-47
        $return .= $vol->getPeriodeDeVol()->getDecollage()->format('Hi');
        //Type 3 heure de départ avion caractère 48-52
        $return .= "+0".$this->decallageGMT($message['dateDebut'])."00";
        //Type 3 Terminal de départ caractère 53-54, conditionnel
        $return .= str_pad($vol->getLigne()->getAeroportDepart()->getTerminal(),2," ",STR_PAD_RIGHT);
        //Type 3 code aéroport d'arrivée caractère 55-57
        $return .= $vol->getLigne()->getAeroportArrivee()->getCodeIATA();
        //Type 3 heure de départ passager caractère 58-61
        $return .= $vol->getPeriodeDeVol()->getAtterissage()->format('Hi');
        //Type 3 heure d'arrivée avion caractère 62-65
        $return .= $vol->getPeriodeDeVol()->getAtterissage()->format('Hi');
        //Type 3 heure d'arrivée avion caractère 67-70
        $return .= "+0".$this->decallageGMT($message['dateFin'])."00";
        //Type 3 Terminal d'arrivée caractère 71-72, conditionnel
        $return .= str_pad($vol->getLigne()->getAeroportArrivee()->getTerminal(),2," ",STR_PAD_RIGHT);
        //Type 3 Type d'avion caractère 73-75, conditionnel
        $return .= $vol->getAvion()->getTypeAvion()->getCodeIATA();
        //Type 3 PRBD caractère 76-95, conditionnel
        //Ne marche pas dans ROCADE
//        $return .= str_pad("Y".$vol->getAvion()->getTypeAvion()->getCapaciteSiege(),20," ",STR_PAD_RIGHT);
        $return .= str_pad("Y",20," ",STR_PAD_RIGHT);
        //Type 3 PRBM caractère 96-100, conditionnel, peut être des blancs
        $return .= $this->genererBlanc(5);
        //Type 3 Service à bord caractère 101-110, optionnel, peut être des blancs
        $return .= $this->genererBlanc(10);
        //Type 3 Joint Operation Airline Designators caractère 111-119, conditionnel, peut être des blancs
        $return .= $this->genererBlanc(9);
        //Type 3 Minimum connecting time caractère 120-121, optionnel, peut être des blancs
        $return .= $this->genererBlanc(2);
        //Type 3 Des blancs caractère 122-127
        $return .= $this->genererBlanc(6);
        //Type 3 Chiffre des centaines pour les n° de séquence de la période de vol caractère 128-128
        if($sequence>=100){
            $return .= substr($sequence,0,1);
        }else{
            $return .= $this->genererBlanc(1);
        }
        //Type 3 Propriétaire de l'avion caracère 129-131 => il faut rajouter un blanc car la compagnie est sur 2 caractères
        $return .= str_pad(self::compagnie,3," ",STR_PAD_RIGHT);
        //Type 3 Compagnie gestionnaire PNT 132-134 => il faut rajouter un blanc car la compagnie est sur 2 caractères
        $return .= str_pad(self::compagnie,3," ",STR_PAD_RIGHT);
        //Type 3 Compagnie gestionnaire PNC caracère 135-137 => il faut rajouter un blanc car la compagnie est sur 2 caractères
        $return .= str_pad(self::compagnie,3," ",STR_PAD_RIGHT);

        //Type 3 Prochain numero de vol caracère 141-144
        if($message['numeroSuivant']){
            //Type 3 Compagnie exploitante du prochain vol caracère 138-140 => il faut rajouter un blanc car la compagnie est sur 2 caractères
            $return .= str_pad(self::compagnie,3," ",STR_PAD_RIGHT);
            $return .= str_pad($message['numeroSuivant'],4," ",STR_PAD_LEFT);
        }else{
            $return .= $this->genererBlanc(7);
        }

        /**
         * @todo calculer durée entre le vol courant et le prochain vol
         */
        //Type 3 Aircraft Rotation Layover caractère 145-145, conditionnel
        if(array_key_exists("jourLePlusProche",$message)){
            $return .= $message['differenceEntreVol'];
        }else{
            $return .= " ";
        }



        //Type 3 Suffice du prochain vol caractère 146-146, conditionnel, blanc par défaut
        $return .= $this->genererBlanc(1);
        //Type 3 Duplicate/Non operational Leg Marker caractère 147-147, conditionnel, blanc par défaut
        $return .= $this->genererBlanc(1);
        //Type 3 Flight Transit Layover caractère 148-148, blanc par défaut
        $return .= $this->genererBlanc(1);
        //Type 3 Code sharing caractère 149-149, blanc par défaut
        $return .= $this->genererBlanc(1);
        //Type 3 Restriction de trafic caractère 150-160, conditionnel, blanc par défaut
        $return .= $this->genererBlanc(11);
        //Type 3 Indicateur de restriction de trafic caractère 161-161, conditionnel, blanc par défaut
        $return .= $this->genererBlanc(1);
        //Type 3 Des blancs caractère 162-172
        $return .= $this->genererBlanc(11);
        //Type 3 Version physique Pax + version d'exploitation soute + VV + version Ariflite
        //Ne marche pas dans ROCADE
//        $return .= str_pad("Y".$vol->getAvion()->getTypeAvion()->getCapaciteSiege()."VV".$vol->getAvion()->getTypeAvion()->getCodeIATA()."-Y".$vol->getAvion()->getTypeAvion()->getCapaciteSiege(),20," ",STR_PAD_RIGHT);
        $return .= str_pad("Y".$vol->getAvion()->getTypeAvion()->getCapaciteSiege()."VV".$vol->getAvion()->getTypeAvion()->getCodeIATA()."-Y".$vol->getAvion()->getTypeAvion()->getCapaciteSiege(),20, " ",STR_PAD_RIGHT);
        //Type 3 Bilateral Information caractère 193-194, optionnel, des blancs
        $return .= $this->genererBlanc(2);
        //Type 3 N° du record caractère 195-200
        $return .= str_pad($record,6,0,STR_PAD_LEFT);
        $return .= "\r\n";

        return $return;
    }

    /**
     * @param $return
     */
    private function addType4($message, $return, $sequence, $record)
    {
        $uneLigne = "";
        /** @var Vol $vol */
        $vol = $message['vol'];
        //Type 4 en tête caractère de 1-1
        $uneLigne .= 4;
        //Type 4 suffixe opérationnel, champ conditionnel, peut être un blanc caractère de 2-2
        $uneLigne .= $this->genererBlanc(1);
        //Type 4 Compagnie diffusée caractère 3-5) => il faut rajouter un blanc car la compagnie est sur 2 caractères
        $uneLigne .= self::compagnie;
        $uneLigne .= $this->genererBlanc(1);
        //Type 4 Numéro de Vol caratère 6-9, cadrée à droite
        $uneLigne .= str_pad($message["numero"],4," ",STR_PAD_LEFT);
        //Type 4 n° séquence de la période de vol
        $sequence2 = $sequence;
        if(strlen($sequence2)>2){
            $sequence2 = substr($sequence2,1,2);
        }
        $uneLigne .= str_pad($sequence2,2,0,STR_PAD_LEFT);
        //Type 4 n° de séquence d utronçon caractère 12-13, valeur par défaut
        $uneLigne .= "01";
        //Type 4 Type de service IATA, caractère 14-14, valeur par défaut
        $uneLigne .= $vol->getTypeDeVol()->getCodeService();
        //Type 4 Des blancs caractère 15-27
        $uneLigne .= $this->genererBlanc(13);
        //Type 4 Chiffre des centaines pour les n° de séquence de la période de vol caractère 28-28
        if($sequence>=100){
            $uneLigne .= substr($sequence,0,1);
        }else{
            $uneLigne .= $this->genererBlanc(1);
        }
        //Type 4 Séquence alpha des escales de départ caractère 29-29, valeur par défaut
        $uneLigne .= "A";
        //Type 4 Séquence alpha des escales de départ caractère 30-30, valeur par défaut
        $uneLigne .= "B";
        //Type 4 Séquence alpha des escales de départ caractère 31-33, valeur par défaut
        $uneLigne .= "010";
        //Type 4 code aéroport de départ caractère 34-36
        $uneLigne .= $vol->getLigne()->getAeroportDepart()->getCodeIATA();
        //Type 4 code aéroport d'arrivée caractère 34-36
        $uneLigne .= $vol->getLigne()->getAeroportArrivee()->getCodeIATA();
        //Type 4 Données caractère 40-194, pour nous les codes shares + des blancs
        /** @var CodeShareVol $codeSahre */
        $sCodeShare = "";
        foreach ($vol->getCodesShareVol() as $codeSahre){
            $sCodeShare .= strtoupper(substr($codeSahre->getLibelle(),0,2))." ".substr($codeSahre->getLibelle(),2)." /";
        }
        $sCodeShare =  substr($sCodeShare,0,-2);
        $uneLigne .= $sCodeShare;

        //Type 4 Des blans jusqu'au caractère 194 inclus
        $uneLigne .= $this->genererBlanc(200 - strlen($uneLigne)-6);

        //Type 4 N° du record caractère 195-200
        $uneLigne .= str_pad($record,6,0,STR_PAD_LEFT);
        $uneLigne .= "\r\n";

        $return .= $uneLigne;
        return $return;
    }

    private function addType5($return,$record,$sommeType3Type4)
    {

        if($sommeType3Type4 % 5 != 0){
            if($sommeType3Type4<5){
                $end = 5-$sommeType3Type4;
            }else{
                $end = 5-$sommeType3Type4 % 5;
            }
            for($i=1;$i<=$end;$i++)
            {
                $return .= $this->genererZero(200)."\r\n";
            }
        }

        //Type 5 code du type caractère 1
        $return .= "5";
        //Type 5 1 blanc caractère 2
        $return .= $this->genererBlanc(1);
        //Type 5 compagnie diffusé cadré à gauche caractère 3-5 => il faut rajouter un blanc car la compagnie est sur 2 caractères
        $return .= self::compagnie;
        $return .= $this->genererBlanc(1);
        //Type 5 date de diffusion JJMMMAA avec le mois en anglais caractère 6-12
        $now = new \DateTime();
        $return .= strtoupper($now->format('dMy'));
        //Type 5 175 blanc caractère 13-187
        $return .= $this->genererBlanc(175);
        //Type 5 last record caractère 188-193
        $return .= str_pad($record,6,0,STR_PAD_LEFT);
        //Type 5 E pour End ou C pour Continue caractère 194-194
        $return .= "E";
        //Type 5 record caractère 195-200
        $return .= str_pad($record+1,6,0,STR_PAD_LEFT);
        $return .= "\r\n";
        //Type 5 suivi de 4 lignes de 0
        for ($i=1;$i<=4;$i++)
        {
            $return .= $this->genererZero(200)."\r\n";
        }

        return $return;
    }

    /**
     * @param $nombreDeZero
     * @return string
     */
    private function genererZero($nombreDeZero){
        $chaine = "";
        for($i=0;$i<$nombreDeZero;$i++){
            $chaine .= "0";
        }
        return $chaine;
    }

    /**
     * @param $nombreDeBlanc
     * @return string
     */
    private function genererBlanc($nombreDeBlanc, $caractereBlanc = " "){
        $chaine = "";
        for($i=0;$i<$nombreDeBlanc;$i++){
            $chaine .= $caractereBlanc;
        }
        return $chaine;
    }

    private function retourChariot($code = "\r\n"){
        return $code;
    }

    private function decallageGMT(\DateTime $date){
        $date = clone $date;
        $date->setTime(4,0,0);
        if($date->format('I') == 1){
            return 2;
        }else{
            return 1;
        }
    }

    /**
     * @param $em EntityManager
     * @param $debut
     * @param $fin
     * @return mixed
     */
    private function getSaisonIATA($em, $debut, $fin){
        /** @var SaisonRepository $repoVol */
        $repoSaison = $em->getRepository('AirCorsicaXKPlanBundle:Saison');

        return $repoSaison->getSaisonIATA($debut, $fin);
    }
}