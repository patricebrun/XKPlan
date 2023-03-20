<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use League\Period\Period;
use Symfony\Component\Asset\Exception\LogicException;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * PeriodeDeVol
 *
 * @ORM\Table(name="periode_de_vol")
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\PeriodeDeVolRepository")
 */
class PeriodeDeVol extends Periode
{
    /**
     * @var array
     *
     * @ORM\Column(name="joursDeValidite", type="simple_array")
     */
    private $joursDeValidite;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="decollage", type="time")
     */
    private $decollage;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="atterissage", type="time")
     */
    private $atterissage;


    /**
     * @var string pendingCancel | pendingSend | send | cancel
     *
     * @ORM\Column(name="etat", type="string", options={"default":"pendingSend"})
     */
    private $etat;

    /**
     * @var Vol
     *
     * @ORM\OneToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\Vol", mappedBy="periodeDeVol", cascade={"persist"})
     */
    private $vol;

    /**
     * @var int
     */
    private $GMT = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="deleste", type="boolean")
     */
    private $deleste = 0;

    /**
     * PeriodeDeVol constructor.
     * @param string $sDateDebut => format yyyy-mm-dd
     * @param string $sDateFin => format yyyy-mm-dd
     * @param string $sDecollage => format HH:MM:SS
     * @param string $sAtterissage => format HH:MM:SS
     * @param array $joursDeValidite
     */
    function __construct($sDateDebut=null, $sDateFin=null, $sDecollage=null, $sAtterissage = null, array $joursDeValidite = array(), $etat = "pendingSend")
    {
        parent::__construct($sDateDebut, $sDateFin);
        $this->decollage   = new \DateTime($sDecollage);
        $this->atterissage = new \DateTime($sAtterissage);
        $this->joursDeValidite = $joursDeValidite;
        $this->etat = $etat;
    }

    /**
     * toString
     * @return string
     */
    public function __toString() {
        return 'du '.$this->getDateDebut()->format('d/m/Y').' au '.$this->getDateFin()->format('d/m/Y');
    }

    /**
     * Set joursDeValidite
     *
     * @param array $joursDeValidite
     *
     * @return PeriodeDeVol
     */
    public function setJoursDeValidite($joursDeValidite)
    {
        if(sizeof($joursDeValidite) != 7){
            $this->joursDeValidite = self::joursToValuesNoIndex($joursDeValidite);
        }else{
            $this->joursDeValidite = $joursDeValidite;
        }
        return $this;
    }

    /**
     * Get joursDeValidite
     *
     * @return array
     */
    public function getJoursDeValidite()
    {
        return $this->joursDeValidite;
    }

    /**
     * Set decollage
     *
     * @param \DateTime $decollage
     *
     * @return PeriodeDeVol
     */
    public function setDecollage($decollage)
    {
        $this->decollage = $decollage;

        return $this;
    }

    /**
     * Get decollage
     *
     * @return \DateTime
     */
    public function getDecollage()
    {
        return $this->decollage;
    }

    /**
     * Set atterissage
     *
     * @param \DateTime $atterissage
     *
     * @return PeriodeDeVol
     */
    public function setAtterissage($atterissage)
    {
        $this->atterissage = $atterissage;

        return $this;
    }

    /**
     * Get atterissage
     *
     * @return \DateTime
     */
    public function getAtterissage()
    {
        return $this->atterissage;
    }

    /**
     * @return int
     */
    public function getGMT()
    {
        return $this->GMT;
    }

    /**
     * @param int $GMT
     */
    public function setGMT($GMT)
    {
        $this->GMT = $GMT;

        $decolage = $this->decollage;
        $atterissage = $this->atterissage;

        if($GMT<0)
        {
            $this->getDateFin()->setTime($this->atterissage->format("H"),$this->atterissage->format("i"),$this->atterissage->format("s"));
            $this->getDateDebut()->setTime($this->decollage->format("H"),$this->decollage->format("i"),$this->decollage->format("s"));

            $this->setDecollage($decolage->sub(new \DateInterval('PT'.abs($GMT).'H')));
            $this->setAtterissage($atterissage->sub(new \DateInterval('PT'.abs($GMT).'H')));

            $this->setDateDebut($this->getDateDebut()->sub(new \DateInterval('PT'.abs($GMT).'H')));
            $this->setDateFin($this->getDateFin()->sub(new \DateInterval('PT'.abs($GMT).'H')));
        }
        if($this->getGMT()>0){
            $this->getDateDebut()->setTime($this->decollage->format("H"),$this->decollage->format("i"),$this->decollage->format("s"));
            $this->getDateFin()->setTime($this->atterissage->format("H"),$this->atterissage->format("i"),$this->atterissage->format("s"));

            $this->setDecollage($decolage->add(new \DateInterval('PT'.abs($GMT).'H')));
            $this->setAtterissage($atterissage->add(new \DateInterval('PT'.abs($GMT).'H')));

            $this->setDateDebut($this->getDateDebut()->add(new \DateInterval('PT'.abs($GMT).'H')));
            $this->setDateFin($this->getDateFin()->add(new \DateInterval('PT'.abs($GMT).'H')));

        }

        return $this;
    }


    /**
     * Set createur
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Utilisateur $createur
     *
     * @return PeriodeDeVol
     */
    public function setCreateur(\AirCorsica\XKPlanBundle\Entity\Utilisateur $createur)
    {
        $this->createur = $createur;

        return $this;
    }

    /**
     * Get createur
     *
     * @return \AirCorsica\XKPlanBundle\Entity\Utilisateur
     */
    public function getCreateur()
    {
        return $this->createur;
    }


    /**
     * Set etat
     *
     * @param string $etat
     *
     * @return PeriodeDeVol
     */
    public function setEtat($etat)
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * Get etat
     *
     * @return string
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * Set vol
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Vol $vol
     *
     * @return PeriodeDeVol
     */
    public function setVol(\AirCorsica\XKPlanBundle\Entity\Vol $vol = null)
    {
        $this->vol = $vol;

        return $this;
    }

    /**
     * Get vol
     *
     * @return \AirCorsica\XKPlanBundle\Entity\Vol
     */
    public function getVol()
    {
        return $this->vol;
    }

    /**
     *
     *
     * @param PeriodeDeVol $periode
     * @param Vol $volOrigine
     * @param string $type vol | volHistorique
     * @return array | null
     */
    public function updateJoursDeValidite(PeriodeDeVol $periode,$volOrigine, $type){
        extract($periode->diffJoursDeValidite($this));
        if('vol' == $type){
            $volComplementaire = clone $volOrigine;
        }else{
            $volComplementaire = VolHistorique::createFromVol($volOrigine);
        }

        switch ($typeOperation) {
            case 'add':
                $aVolsComplementaires[] = $this->addJoursValidite($periode,$volComplementaire,$newValues);
                break;
            case 'delete':
                $aVolsComplementaires[] = $this->deleteJoursValidite($periode,$volComplementaire,$newValues);
                break;
            case 'merge':
                $aVolsComplementaires = $this->mergeJoursValidite($periode,$volComplementaire,$newValues);
                break;
            default:
                return null;
                break;
        }

        return $aVolsComplementaires;
    }

    /**
     *
     *
     * @param PeriodeDeVol $periode
     * @param Vol $volComplementaire
     * @param array $newValues
     * @return Vol
     */
    private function addJoursValidite(PeriodeDeVol $periode,Vol $volComplementaire,array $newValues){
        $periode->setEtat("send");
        $periode->mergeJoursToDelete($newValues);
        $volComplementaire->getPeriodeDeVol()->setJoursDeValidite($newValues);
        $volComplementaire->getPeriodeDeVol()->setEtat('pendingSend');

        return $volComplementaire;
    }

    /**
 *
 *
 * @param PeriodeDeVol $periode
 * @param Vol $volComplementaire
 * @param array $newValues
 * @return Vol
 */
    private function deleteJoursValidite(PeriodeDeVol $periode,Vol $volComplementaire,array $newValues){
//        $volComplementaire->getPeriodeDeVol()->setEtat("send");
//        $volComplementaire->getPeriodeDeVol()->mergeJoursToDelete($newValues);
//        $periode->setJoursDeValidite($newValues);
//        $periode->setEtat("pendingCancel");



        $periode->setEtat("send");
        $periode->mergeJoursToDelete($newValues);
        $volComplementaire->getPeriodeDeVol()->setJoursDeValidite($newValues);
        $volComplementaire->getPeriodeDeVol()->setEtat("pendingCancel");


        return $volComplementaire;
    }

    /**
     *
     *
     * @param PeriodeDeVol $periode
     * @param Vol $volComplementaire
     * @param array $newValues
     * @return array
     */
    private function mergeJoursValidite(PeriodeDeVol $periode,Vol $volComplementaire,array $newValues){
       $res = array();
       $newVolComplementaire = clone $volComplementaire;
       $newPeriode = clone $periode;
       $periode->mergeJoursToDelete($newValues['add']);
       $res[] = $this->deleteJoursValidite($periode,$volComplementaire,$newValues['del']);
       $newPeriode->setJoursDeValidite($newValues['add']);
       $newVolComplementaire->setPeriodeDeVol($newPeriode);
       $res[] = $newVolComplementaire;

       return $res;
    }


    /**
     * mise à jour de la propriété joursValidite en mode delete historisation
     *
     * @param array $values
     * @return void
     */
    public function mergeJoursToDelete(array $values){
        $res = array();
        $currentValues = $this->getJoursDeValidite();

        foreach ($values as $key => $value){
            if ("-" == $value){
                $res[$key] = $currentValues[$key];
            }else{
                $res[$key] = "-";
            }
        }

        $this->setJoursDeValidite($res);

        return;
    }


    /**
     * Retourne un tableau associatif avec en clé le type d'opération 'add'|'delete'|'merge'
     * et en valeur un tableau de jours pour la nouvelle période
     *
     * @param PeriodeDeVol $periode
     * @return array
     */
    public function diffJoursDeValidite(PeriodeDeVol $periode){
        $ajout = false;
        $delete = false;

        foreach ($this->joursDeValidite as $key => $value){
            if($periode->joursDeValidite[$key] != $value){
                if("-" == $periode->joursDeValidite[$key]){
                    $ajout = true;
                    $aAjouts[$key] = $value;
                }else{
                    $delete = true;
                    $aDeletes[$key] = $periode->joursDeValidite[$key];
                }
            }
        }

        if(true == $ajout && true == $delete){
            return array(
                'typeOperation' => 'merge',
                'newValues'     => array(
                    "add" => PeriodeDeVol::joursToValues($aAjouts),
                    "del" => PeriodeDeVol::joursToValues($aDeletes),
                    ),
                );
        }

        if(true == $delete){
            return array(
                'typeOperation' => 'delete',
                'newValues'     => PeriodeDeVol::joursToValues($aDeletes),
                );
        }

        if(true == $ajout){
            return array(
                'typeOperation' => 'add',
                'newValues'     => PeriodeDeVol::joursToValues($aAjouts),
                );
        }

        return array(
            'typeOperation' => 'equal',
            'newValues'     => $this->joursDeValidite,
            );
    }

    /**
     * transforme un tableau associatif de jours  en tableau de jours compatible JoursDeValidite => ex [2=>3,3=>4,4=>5,6=>7] to [-,-,3,4,5,-,7]
     *
     * @param array $jours
     * @return array
     */
    private static function joursToValues(array $jours){
        $res = array('-','-','-','-','-','-','-');
        foreach ($jours as $key => $value){
            $res[$key] = $value;
        }
        return $res;
    }

    /**
     * transforme un tableau de jours  en tableau de jours compatible JoursDeValidite => ex [3,4,5] to [-,-,3,4,5,-,7]
     *
     * @param array $jours
     * @return array
     */
     static function joursToValuesNoIndex(array $jours){
        $res = array('-','-','-','-','-','-','-');
        foreach ($jours as $key => $value){
            $res[$value - 1] = $value ;
        }
        return $res;
    }


    /**
     * transforme un periodeDeVol->joursDeValidite cad par ex ['-','2','-','-','5','6','-'] en chaine utilisable dans les message SCR soit -2--56-
     *
     * @return string
     */
    public function joursDeValiditeArrayToSCRMessageString(){
        $joursdevalidite = $this->getJoursDeValidite();
        $res="";
        foreach ($joursdevalidite as $jour){
            $res .=$jour;
        }
        return $res;
    }

    /**
     * transforme un periodeDeVol->joursDeValidite cad par ex ['-','2','-','-','5','6','-'] en chaine utilisable dans les message SSM soit 256
     *
     * @return string
     */
    public function joursDeValiditeArrayToSSMMessageString(){
        $joursdevalidite = $this->getJoursDeValidite();
        $res="";
        foreach ($joursdevalidite as $jour){
            if($jour!='-') {
                $res .= $jour;
            }
        }
        return $res;
    }

    /**
     * Update periodeVol from periodeVol
     *
     * @param PeriodeDeVol $periode
     * @return PeriodeDeVol $this
     */
    public function updatePeriode(PeriodeDeVol $periode)
    {
        foreach ($periode->asArray() as $name => $value){
            if($name == 'id' || '_' == $name[0]){
                continue;
            }
            if(preg_match("%date%",$name)){
                $name = "set".ucfirst($name);
                $this->$name($value);
            }else{
                $this->$name = $value;
            }

        }

        return $this;
    }


    public function asArray(){
        $properties = [];
        $properties = parent::asArray();
        foreach ($this as $name => $value){
            $properties[$name] = $value;
        }
        return $properties;
    }


    /**
     * Set deleste
     *
     * @param boolean $deleste
     *
     * @return PeriodeDeVol
     */
    public function setDeleste($deleste)
    {
        $this->deleste = $deleste;

        return $this;
    }

    /**
     * Get deleste
     *
     * @return boolean
     */
    public function getDeleste()
    {
        return $this->deleste;
    }

    /**
     * Permet de tester si this est fusionnable avec la période passée en paramètre
     * @param PeriodeDeVol $periodeDeVolSuivante
     * @return bool|int
     */
    public function estFusionnable(PeriodeDeVol $periodeDeVolSuivante){
	    $sub = new \DateInterval("P1D");
	    $add = new \DateInterval("P1D");


	    if($this->getDateDebut()>$this->getDateFin()){
		    $this->getDateFin()->add($add);
	    }
        /** @var \League\Period\Period $periodeCourante */
        $periodeCourante        = new Period($this->getDateDebut(),$this->getDateFin());

	    if($periodeDeVolSuivante->getDateDebut()>$periodeDeVolSuivante->getDateFin()){
		    $periodeDeVolSuivante->getDateFin()->add($add);
	    }

        /** @var \League\Period\Period $periodeSuivante */
        $periodeSuivante        = new Period($periodeDeVolSuivante->getDateDebut(),$periodeDeVolSuivante->getDateFin());

        $refJourValiditeCourant = $this->joursDeValidite;
        $refJourValiditeSuivant = $periodeDeVolSuivante->joursDeValidite;

        $jourValiditeAExclureCourant = array();
        $jourValiditeAExclureSuivant = array();
        $jourValiditeAExclureTotalement = array();

        for($i=0;$i<=6;$i++){
            $j = $refJourValiditeCourant[$i];
            if($j != "-" && !in_array($j,$refJourValiditeSuivant)){ $jourValiditeAExclureCourant[$i] = $j;}
            if($j != "-"){ $jourValiditeAExclureTotalement[$i] = $j; }

            $j = $refJourValiditeSuivant[$i];
            if($j != "-" && !in_array($j,$refJourValiditeCourant)){ $jourValiditeAExclureSuivant[$i] = $j;}
            if($j != "-"){ $jourValiditeAExclureTotalement[$i] = $j; }
        }



        //Un trou entre les périodes
        if($periodeSuivante->getStartDate() > $periodeCourante->getEndDate()){
            $periodeAvant = new PeriodeDeVol($periodeCourante->getStartDate()->format('y-m-d'),$periodeCourante->getEndDate()->format('y-m-d'));
            $periodeEntre = new PeriodeDeVol($periodeCourante->getEndDate()->add($add)->format('y-m-d'),$periodeSuivante->getStartDate()->sub($sub)->format('y-m-d'));
            $periodeApres = new PeriodeDeVol($periodeSuivante->getStartDate()->format('y-m-d'),$periodeSuivante->getEndDate()->format('y-m-d'));

            if($periodeEntre->getDateDebut()>$periodeEntre->getDateFin()){
                $periodeEntre = NULL; //Pas de traitement dans ce cas
            }else{
                $startDate = clone $periodeEntre->getDateDebut();
                while( $startDate <= $periodeEntre->getDateFin()){
                    $d = $startDate->format('N');
                    if(in_array($d,$jourValiditeAExclureTotalement)){ //On ne peut pas fusionner
                        return FALSE;
                    }
                    $startDate->add($add);
                }
            }

            $startDate = clone $periodeAvant->getDateDebut();
            while( $startDate <= $periodeAvant->getDateFin()){
                $d = $startDate->format('N');
                if(in_array($d,$jourValiditeAExclureSuivant)){ //On ne peut pas fusionner
                    return FALSE;
                }
                $startDate->add($add);
            }

            $startDate = clone $periodeApres->getDateDebut();
            while( $startDate <= $periodeApres->getDateFin()){
                $d = $startDate->format('N');
                if(in_array($d,$jourValiditeAExclureCourant)){ //On ne peut pas fusionner
                    return FALSE;
                }
                $startDate->add($add);
            }
            return 2;
        }
        //La periode suivante chevauche la date de fin de this
        if($periodeCourante->getEndDate() > $periodeSuivante->getStartDate()
                        && $periodeSuivante->getEndDate() > $periodeCourante->getEndDate()){
            $periodeAvant = new PeriodeDeVol($periodeCourante->getStartDate()->format('y-m-d'),$periodeSuivante->getStartDate()->sub($sub)->format('y-m-d'));
            $periodeApres = new PeriodeDeVol($periodeCourante->getEndDate()->add($add)->format('y-m-d'),$periodeSuivante->getEndDate()->format('y-m-d'));

            $startDate = clone $periodeAvant->getDateDebut();
            while( $startDate <= $periodeAvant->getDateFin()){
                $d = $startDate->format('N');
                if(in_array($d,$jourValiditeAExclureSuivant)){//On ne peut pas fusionner
                    return FALSE;
                }
                $startDate->add($add);
            }

            $startDate = clone $periodeApres->getDateDebut();
            while( $startDate <= $periodeApres->getDateFin()){
                $d = $startDate->format('N');
                if(in_array($d,$jourValiditeAExclureCourant)){//On ne peut pas fusionner
                    return FALSE;
                }
                $startDate->add($add);
            }
            return 2;
        }
        //Période suivante est contenue dans this mais avec dateFin égale
        if($periodeSuivante->getStartDate()>$periodeCourante->getStartDate()
                        && $periodeSuivante->getEndDate() == $periodeCourante->getEndDate()){
            $periodeAvant = new PeriodeDeVol($periodeCourante->getStartDate()->format('y-m-d'),$periodeSuivante->getStartDate()->sub($sub)->format('y-m-d'));

            $startDate = clone $periodeAvant->getDateDebut();
            while( $startDate <= $periodeAvant->getDateFin()){
                $d = $startDate->format('N');
                if(in_array($d,$jourValiditeAExclureSuivant)){//On ne peut pas fusionner
                    return FALSE;
                }
                $startDate->add($add);
            }
            return 2;
        }
        //Période suivante chevauche la dateFin de this et avec dateDebut égale
        if($periodeCourante->getStartDate() == $periodeSuivante->getStartDate()
            && $periodeSuivante->getEndDate() > $periodeCourante->getEndDate()){
            $periodeApres = new PeriodeDeVol($periodeCourante->getEndDate()->add($add)->format('y-m-d'),$periodeSuivante->getEndDate()->format('y-m-d'));

            $startDate = clone $periodeApres->getDateDebut();
            while( $startDate <= $periodeApres->getDateFin()){
                $d = $startDate->format('N');
                if(in_array($d,$jourValiditeAExclureCourant)){//On ne peut pas fusionner
                    return FALSE;
                }
                $startDate->add($add);
            }
            return 2;
        }
        //Date periode identique
        if($periodeCourante->sameValueAs($periodeSuivante)){
            return 1;
        }
        //Période suivante est contenue dans this mais avec dateDebut égale
        if($periodeCourante->getStartDate() == $periodeSuivante->getStartDate()
                && $periodeCourante->getEndDate() > $periodeSuivante->getEndDate()){
            $periodeApres = new PeriodeDeVol($periodeSuivante->getEndDate()->add($add)->format('y-m-d'),$periodeCourante->getEndDate()->format('y-m-d'));

            $startDate = clone $periodeApres->getDateDebut();
            while( $startDate <= $periodeApres->getDateFin()){
                $d = $startDate->format('N');
                if(in_array($d,$jourValiditeAExclureCourant)){//On ne peut pas fusionner
                    return FALSE;
                }
                $startDate->add($add);
            }
            return 2;
        }
        //La periode suivante est entièrement contenu dans la periode courante
        if($periodeSuivante->getStartDate() > $periodeCourante->getStartDate()
                    && $periodeCourante->getEndDate() > $periodeSuivante->getEndDate()) {
            $periodeAvant = new PeriodeDeVol($periodeCourante->getStartDate()->format('y-m-d'), $periodeSuivante->getStartDate()->sub($sub)->format('y-m-d'));
            $periodeApres = new PeriodeDeVol($periodeSuivante->getEndDate()->add($add)->format('y-m-d'), $periodeCourante->getEndDate()->format('y-m-d'));

            $startDate = clone $periodeAvant->getDateDebut();
            while ($startDate <= $periodeAvant->getDateFin()) {
                $d = $startDate->format('N');
                if (in_array($d, $jourValiditeAExclureSuivant)) {//On ne peut pas fusionner
                    return FALSE;
                }
                $startDate->add($add);
            }

            $startDate = clone $periodeApres->getDateDebut();
            while ($startDate <= $periodeApres->getDateFin()) {
                $d = $startDate->format('N');
                if (in_array($d, $jourValiditeAExclureSuivant)) {//On ne peut pas fusionner
                    return FALSE;
                }
                $startDate->add($add);
            }
            return 2;
        }
        return FALSE;
    }

    /**
     * Permet de fusionner this avec la période en paramètre mais en fonction du résultat de la méthode estFusionnable
     * @param PeriodeDeVol $periodeDeVolSuivante
     * @param $code
     * @return $this|PeriodeDeVol
     */
    public function fusionnerPeriode(PeriodeDeVol $periodeDeVolSuivante, $code){
        switch ($code) {
            case 1: //same
                $aJourValidite = $this->combinerJourDeValidite($periodeDeVolSuivante);
                $this->setJoursDeValidite($aJourValidite);
                return $this;
            break;
            case 2:
                if($this->getDateDebut() > $periodeDeVolSuivante->getDateDebut()){
                    $this->setDateDebut($periodeDeVolSuivante->getDateDebut());
                }else{
                    $this->setDateDebut($this->getDateDebut());
                }
                if($this->getDateFin() > $periodeDeVolSuivante->getDateFin()){
                    $this->setDateFin($this->getDateFin());
                }else{
                    $this->setDateFin($periodeDeVolSuivante->getDateFin());
                }
                $aJourValiditeCourant = $this->joursDeValidite;
                $aJourValiditeSuivant = $periodeDeVolSuivante->joursDeValidite;
                for($i=0;$i<=6;$i++){
                    $d = $aJourValiditeSuivant[$i];
                    if($d != "-"){
                        $aJourValiditeCourant[$i] = $d;
                    }
                }
                return $this->setJoursDeValidite($aJourValiditeCourant);
            break;
            default:
                throw new \LogicException("Pas de valeur autorisée par défaut pour le swith !");
        }
    }

    /**
     * Permet d'injecter les jours de validité de la période en parametre dans this
     * @param PeriodeDeVol $periodeDeVol
     * @return array
     */
    private function combinerJourDeValidite(PeriodeDeVol $periodeDeVol)
    {
        $jourValiditeRef1 = $this->joursDeValidite;
        $jourValiditeRef2 = $periodeDeVol->joursDeValidite;
        $jourValiditeCombine = array();
        foreach ($jourValiditeRef1 as $key => $value){
            if ("-" == $value){
                $jourValiditeCombine[$key] = $jourValiditeRef2[$key];
            }else{
                $jourValiditeCombine[$key] = $value;
            }
        }
        return $jourValiditeCombine;
    }


    /**
     * Test la cohérence d'une période de vol en fonction des jours de validité et de la date de début et de fin
     * @param bool $avecProposition permet de retourner la période corrigée si elle n'est pas cohérente. La correction n'est pas enregistrée en BDD à ce niveau là
     * @return PeriodeDeVol|bool
     */
    public function estCoherente($avecProposition = FALSE){
        $jourValidite = $this->joursDeValidite;

        if(!in_array($this->getDateDebut()->format('N'),$jourValidite)){
            if($avecProposition){
                return $this->corrigerDate();
            }
            return FALSE;
        }
        if(!in_array($this->getDateFin()->format('N'),$jourValidite)){
            if($avecProposition){
                return $this->corrigerDate();
            }
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Corrige une période de vol en déplaçant la date de debut et ou de fin en fonction de ces jours de validité
     * @return $this
     */
    public function corrigerDate($persist = false){
        $jourValidite = $this->joursDeValidite;
        if($persist){
            $correction = $this;
        }else{
            $correction = clone $this;
        }

        $dateDebut = clone $this->getDateDebut();
        $add = new \DateInterval("P1D");
        while ($dateDebut<=$this->getDateFin()){
           if(in_array($dateDebut->format('N'),$jourValidite)){
               $correction->setDateDebut($dateDebut);
               break;
           }
           $dateDebut = $dateDebut->add($add);
        }

        $dateFin = clone $this->getDateFin();
        $sub = new \DateInterval("P1D");
        while ($dateFin>=$this->getDateDebut()){
            if(in_array($dateFin->format('N'),$jourValidite)){
                $correction->setDateFin($dateFin);
                break;
            }
            $dateFin = $dateFin->sub($sub);
        }
        return $correction;
    }

    /**
     * Test simplement si une periode de vol a des jours dce validite
     * @return bool
     */
    public function contientJoursDeValidite(){
        if(sizeof($this->getJoursDeValidite())==0){
            return FALSE;
        }
        foreach ($this->getJoursDeValidite() as $jour){
            if($jour != "-"){
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Test si les jours de validite sont présents dans la période
     * @return bool
     */
    public function isJoursDeValiditeDansPeriode($avecProposition = FALSE){
        $jourValiditePossible = $this->getJourDeValiditePossible();

        $jourValiditeThis = $this->getJoursDeValidite();

        $aNbrOccur = array_count_values($jourValiditeThis);

        if(isset($aNbrOccur['-']) && $aNbrOccur['-'] == 7){
            return FALSE;
        }

        foreach ($jourValiditeThis as $jour){
            if($jour != "-"){
                if(!in_array($jour,$jourValiditePossible)){
                    if($avecProposition){
                        return $this->corrigerJourDeValidite();
                    }
                    return FALSE;
                }
            }
        }
        return TRUE;
    }

    public function corrigerJourDeValidite($persist = false){
        $jourValiditePossible = $this->getJourDeValiditePossible();
        $jourValiditeThis = $this->getJoursDeValidite();
        $jourValiditeCorrige = array();
        $jourString = "";
        foreach ($jourValiditePossible as $jour){
            if(in_array($jour,$jourValiditeThis)){
                $jourValiditeCorrige[] = $jour;
                $jourString .= $jour;
            }
        }
        if($persist){
//            $periodeCorrige = clone $this;
            return $this->setJoursDeValidite($jourValiditeCorrige);
        }else{
            if($jourString == $this->joursDeValiditeArrayToSSMMessageString()) return;
            $periodeCorrige = clone $this;
            return $periodeCorrige->setJoursDeValidite($jourValiditeCorrige);
        }
    }

    public function getJourDeValiditePossible(){
        $dtDebut = clone $this->getDateDebut();
        $testDateFin = clone $this->getDateFin();
        $add = new \DateInterval("P1D");
        $jourValiditePossible = array();
        $i = 1;
        while ($dtDebut <= $testDateFin && $i <= 7) {
            $jourValiditePossible[] = $dtDebut->format("N");
            $dtDebut = $dtDebut->add($add);
            $i++;
        }
        asort($jourValiditePossible);
        return $jourValiditePossible;
    }

    /**
     * Corrige les dates de this en fonction des jours de validite et corrige les jours de validite en fonction des dates
     * @return PeriodeDeVol => une nouvelle période de vol non persisté
     */
    public function corriger($persist = false){
        $newPeriode = $this->corrigerDate($persist);
        $newPeriode->corrigerJourDeValidite($persist);
        return $newPeriode;
    }

    /**
     * Test si l'heure de decollage n'est pas supérieur à l'heure d'arrivée
     * @return bool
     */
    public function testDecollageAtterissage(){
        $atterisage = clone $this->getAtterissage();

        //Il faut tenir compte du fait qu'un vol peut se faire entre 2 jours
        $add = new \DateInterval('P1D');
        $atterisage->add($add);

        $debut = $this->getDecollage()->format('U');
        $fin = $atterisage->format('U');
        $h = ($fin - $debut) / 3600;
        if($this->getDecollage()>=$this->getAtterissage() && $h < 4){
            return TRUE;
        }
        if($this->getDecollage()<=$this->getAtterissage()) {
            return TRUE;
        }
        return FALSE;
    }
}
