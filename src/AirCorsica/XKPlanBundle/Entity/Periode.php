<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use League\Period\Period;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Periode
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @DiscriminatorMap({"periodedevol" = "PeriodeDeVol", "periodeimmobilisation" = "PeriodeImmobilisation", "periode" = "Periode", "periodesaison" = "PeriodeSaison"})
 * @ORM\Table(name="periode")
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\PeriodeRepository")
 */
class Periode extends AbstractXKPlan
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateDebut", type="datetime")
     */
    private $dateDebut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateFin", type="datetime")
     */
    private $dateFin;

    /**
     * Periode constructor.
     * @param string $sDateDebut
     * @param string $sDateFin
     */
    function __construct($sDateDebut, $sDateFin)
    {
        $oDateDebut = new \DateTime($sDateDebut);
        $oDateFin = new \DateTime($sDateFin);
        $this->dateDebut = $oDateDebut;
        $this->dateFin   = $oDateFin;

        return $this;
    }

    function __clone()
    {
        $this->id = null;
        $this->dateDebut = clone $this->dateDebut;
        $this->dateFin   = clone $this->dateFin;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set dateDebut
     *
     * @param \DateTime $dateDebut
     *
     * @return Periode
     */
    public function setDateDebut($dateDebut)
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    /**
     * Get dateDebut
     *
     * @return \DateTime
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * Set dateFin
     *
     * @param \DateTime $dateFin
     *
     * @return Periode
     */
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * Get dateFin
     *
     * @return \DateTime
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }


    /**
     * Si 2 périodes ont les mêmes dates alors true si non  false
     *
     * @param Periode $periode
     * @return bool
     */
    public function equalDates(Periode $periode){
        $periodeRef     = new Period($this->dateDebut,$this->dateFin);
        $periodeCompare = new Period($periode->dateDebut,$periode->dateFin);

        if($periodeRef->sameValueAs($periodeCompare)){
            return true;
        }else{
            return false;
        }
    }


    /**
     * Compare et découpe les périodes
     * @param Periode $periode
     * @param String $etat
     * @return array
     */
    public function comparePeriodes(Periode $periode,$etat="pendingSend"){
        $periodeRef     = new Period($this->dateDebut,$this->dateFin);
        $periodeCompare = new Period($periode->dateDebut,$periode->dateFin);

        //si les 2 périodes sont identiques
        if($periodeRef->sameValueAs($periodeCompare)){
            return array(
                "periodeNew"  => array($etat => $this),
            );
        }

        //si la période est comprise dans la période de référence
        //on rétrécit la période
        if($periodeRef->contains($periodeCompare)){
            $periodeClone = clone $periode;
            $periodeDebut = new Periode(
                $this->getDateDebut()->format("Y-m-d"),
                $periodeClone->getDateDebut()->sub(new \DateInterval("P1D"))->format("Y-m-d"));

            $periodeFin = new Periode(
                $periodeClone->getDateFin()->add(new \DateInterval("P1D"))->format("Y-m-d"),
                $this->getDateFin()->format("Y-m-d"));

            if($periodeRef->getStartDate() < $periodeCompare->getStartDate() && $periodeRef->getEndDate() > $periodeCompare->getEndDate()){
                return array(
                    "periodeDebut" => array("pendingCancel" => $periodeDebut),
                    "periodeNew" => array("pendingSend" =>$periode),
                    "periodeFin" => array("pendingCancel" => $periodeFin),
                );
            }
            //on a réduit la fin
            if($periodeRef->getStartDate() == $periodeCompare->getStartDate()){
                return array(
                    "periodeNew" => array("pendingSend" =>$periode),
                    "periodeFin" => array("pendingCancel" => $periodeFin),
                );
            }
            //On a réduit le début
            if($periodeRef->getEndDate() == $periodeCompare->getEndDate()){
                return array(
                    "periodeDebut" => array("pendingCancel" =>$periodeDebut),
                    "periodeNew" => array("pendingSend" => $periode),
                );
            }
        }

        //si la période de référence est comprise dans la période
        //on élargit la période
        if($periodeCompare->contains($periodeRef)){
            $periodeClone = clone $this;

            $periodeDebut = new Periode(
                $periode->getDateDebut()->format("Y-m-d"),
                $periodeClone->getDateDebut()->sub(new \DateInterval("P1D"))->format("Y-m-d"));

            $periodeFin = new Periode(
                $periodeClone->getDateFin()->add(new \DateInterval("P1D"))->format("Y-m-d"),
                $periode->getDateFin()->format("Y-m-d"));

            if($periodeRef->getStartDate() > $periodeCompare->getStartDate() && $periodeRef->getEndDate() < $periodeCompare->getEndDate()){
                return array(
                    "periodeDebut" => array("pendingSend" => $periodeDebut),
                    "periodeNew" => array("send" =>$this),
                    "periodeFin" => array("pendingSend" => $periodeFin),
                );
            }

            //on a aggrandi la fin
            if($periodeRef->getStartDate() == $periodeCompare->getStartDate()){
                return array(
                    "periodeNew" => array("send" =>$this),
                    "periodeFin" => array("pendingSend" => $periodeFin),
                );
            }
            //On a aggrandi le début
            if($periodeRef->getEndDate() == $periodeCompare->getEndDate()){
                return array(
                    "periodeDebut" => array("pendingSend" =>$periodeDebut),
                    "periodeNew" => array("send" => $this),
                );
            }
        }

        //si la période de référence a des dates communes avec la période
        if($periodeRef->overlaps($periodeCompare)){
            $intersectPeriod = $periodeRef->intersect($periodeCompare);
            $intersectPeriodClone = clone $intersectPeriod;

            $periodeNew = new Periode(
                $intersectPeriodClone->getStartDate()->format("Y-m-d"),
                $intersectPeriodClone->getEndDate()->format("Y-m-d")
            );

            if($this->dateDebut < $periode->dateDebut){
                $periodeDebut = new Periode(
                    $this->getDateDebut()->format("Y-m-d"),
                    $intersectPeriod->getStartDate()->sub(new \DateInterval("P1D"))->format("Y-m-d")
                );

                $periodeFin = new Periode(
                    $intersectPeriod->getEndDate()->add(new \DateInterval("P1D"))->format("Y-m-d"),
                    $periode->getDateFin()->format("Y-m-d")
                );

                return array(
                    "periodeDebut"    => array("pendingCancel" =>$periodeDebut),
                    "periodeNew"    => array("pendingSend" =>$periodeNew),
                    "periodeFin" => array("pendingSend" =>$periodeFin),
                );
            }else{

                $periodeDebut = new Periode(
                    $periode->getDateDebut()->format("Y-m-d"),
                    $intersectPeriod->getStartDate()->sub(new \DateInterval("P1D"))->format("Y-m-d")
                );

                $periodeFin = new Periode(
                    $intersectPeriod->getEndDate()->add(new \DateInterval("P1D"))->format("Y-m-d"),
                    $this->getDateFin()->format("Y-m-d")
                );

                return array(
                    "periodeDebut"    => array("pendingSend" =>$periodeDebut),
                    "periodeNew"    => array("pendingSend" =>$periodeNew),
                    "periodeFin" => array("pendingCancel" =>$periodeFin),
                );
            }
        }

        if($periodeCompare->getEndDate() == $periodeCompare->getStartDate() &&
            $periodeCompare->getEndDate() >= $periodeRef->getEndDate() &&
            $periodeCompare->getStartDate() <= $periodeRef->getEndDate()){

            $periodeClone = clone $periode;
            $periodeDebut = new Periode(
                $this->getDateDebut()->format("Y-m-d"),
                $periodeClone->getDateDebut()->sub(new \DateInterval("P1D"))->format("Y-m-d"));

            //On a réduit le début
            if($periodeRef->getEndDate() == $periodeCompare->getEndDate()){
                return array(
                    "periodeDebut" => array("pendingCancel" =>$periodeDebut),
                    "periodeNew" => array("pendingSend" => $periode),
                );
            }

        }

        //si les périodes sont différentes
        return array(
            "deleteNew"    => array("pendingSend" =>$periode,"pendingCancel" =>$this),
        );
    }

    public function setDates(Periode $periode){
        $this->dateDebut = $periode->getDateDebut();
        $this->dateFin = $periode->getDateFin();
    }


    /**
     * est-ce qu'une periode est à cheval sur 2 années ?
     * @return bool
     */
    public function overlapsYear(){
        if($this->getDateDebut()->format('Y') == $this->getDateFin()->format('Y')){
            return false;
        }else{
            return true;
        }
    }

    /**
     * retourne dans un hash le nombre de semaines complètes('w') et le nombre de jours restants('d')
     * @return array
     * @throws Exception
     */
    public function getNumberWeekAndDay(){
        if($this->getDateDebut() < $this->getDateFin()){
            $firstLundi  = clone $this->getDateDebut();
            if($firstLundi->format("l")!="Monday") {
                $firstLundi->modify('next monday');
            }

            $lastDimanche  = clone $this->getDateFin();
            if($lastDimanche->format("l")!="Sunday") {
                $lastDimanche->modify('previous sunday');
            }



            $nbWeeks =  ceil(($firstLundi->diff($lastDimanche)->days)/7);
            $daysBefore =  $this->getDateDebut()->diff($firstLundi)->days;
            $daysAfter =  $lastDimanche->diff($this->getDateFin())->days;
            if(0 == $nbWeeks){
                $daysAfter ++;
            }
            return array('w'=>$nbWeeks,"d"=>$daysBefore + $daysAfter );
        }else{
            throw new Exception("Erreur : la date de début doit être inférieure à la date de fin.");
        }
    }

    public function asArray(){
        $properties = [];
        foreach ($this as $name => $value){
            $properties[$name] = $value;
        }
        return $properties;
    }
}

