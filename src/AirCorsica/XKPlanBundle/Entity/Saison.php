<?php

namespace AirCorsica\XKPlanBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Saison
 *
 * @ORM\Table(name="saison")
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\SaisonRepository")
 */
class Saison extends AbstractXKPlan
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
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="descriptif", type="text", nullable=true)
     */
    private $descriptif;

    /**
     * @var bool
     *
     * @ORM\Column(name="visibleMenuPopup", type="boolean")
     */
    private $visibleMenuPopup = 1;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AirCorsica\XKPlanBundle\Entity\PeriodeSaison", orphanRemoval=true, mappedBy="saison", cascade={"persist"})
     */
    private $periodesSaison;

    /**
     * Periode de base
     * @var Periode
     *
     * @ORM\OneToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\Periode", cascade={"persist", "remove"})
     */
    private $periode;

    /**
     * Saison constructor.
     * @param string $annee format = yyyy
     * @param string $saison ete|hiver
     * @return Saison
     */
    public function __construct($annee=null, $saison=null)
    {
        $libelleSaison = "ete" == $saison ? 'Eté' : 'Hiver';
        $lettreCodeIata= "ete" == $saison ? 'S' : 'W';

        $this->nom = $lettreCodeIata.substr($annee,-2);
        //Pour l'import
        /**
         * @todo la condition peut être supprimé après l'import
         */
        if($annee!==null && $saison !==null)
            $this->periode = self::getDateSaisonIATA($annee, $saison);
        $this->periodesSaison = new ArrayCollection();
        $this->descriptif = "Saison ".$annee. " - ".$libelleSaison." IATA";

        return $this;
    }

    /**
     * toString
     * @return string
     */
    public function __toString()
    {
        return $this->getNom();
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
     * Set nom
     *
     * @param string $nom
     *
     * @return Saison
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set descriptif
     *
     * @param string $descriptif
     *
     * @return Saison
     */
    public function setDescriptif($descriptif)
    {
        $this->descriptif = $descriptif;

        return $this;
    }

    /**
     * Get descriptif
     *
     * @return string
     */
    public function getDescriptif()
    {
        return $this->descriptif;
    }

    /**
     * Set visibleMenuPopup
     *
     * @param boolean $visibleMenuPopup
     *
     * @return Saison
     */
    public function setVisibleMenuPopup($visibleMenuPopup)
    {
        $this->visibleMenuPopup = $visibleMenuPopup;

        return $this;
    }

    /**
     * Get visibleMenuPopup
     *
     * @return bool
     */
    public function getVisibleMenuPopup()
    {
        return $this->visibleMenuPopup;
    }

    /**
     * Add periodesSaison
     *
     * @param \AirCorsica\XKPlanBundle\Entity\PeriodeSaison $periodesSaison
     *
     * @return Saison
     */
    public function addPeriodesSaison(\AirCorsica\XKPlanBundle\Entity\PeriodeSaison $periodesSaison)
    {
        $this->periodesSaison[] = $periodesSaison;

        return $this;
    }

    /**
     * Remove periodesSaison
     *
     * @param \AirCorsica\XKPlanBundle\Entity\PeriodeSaison $periodesSaison
     */
    public function removePeriodesSaison(\AirCorsica\XKPlanBundle\Entity\PeriodeSaison $periodesSaison)
    {
        $this->periodesSaison->removeElement($periodesSaison);
    }

    /**
     * Get periodesSaison
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPeriodesSaison($order = "")
    {
        switch ($order){
            case "IATA":
                $aPeriodeSaison = array();
                /** @var PeriodeSaison $periodeSaison */
                foreach ($this->periodesSaison as $periodeSaison){
                    if($periodeSaison->getIsIATA()){
                        array_unshift($aPeriodeSaison,$periodeSaison);
                    }else{
                        $aPeriodeSaison[] = $periodeSaison;
                    }
                }
                $aCollectionPeriodeSaison = new ArrayCollection();
                foreach ($aPeriodeSaison as $periodeSaison){
                    $aCollectionPeriodeSaison->add($periodeSaison);
                }
                return $aCollectionPeriodeSaison;
            break;
            default:
                return $this->periodesSaison;
        }

    }

    /**
     * Set periode
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Periode $periode
     *
     * @return Saison
     */
    public function setPeriode(\AirCorsica\XKPlanBundle\Entity\Periode $periode = null)
    {
        $this->periode = $periode;

        return $this;
    }

    /**
     * Get periode
     *
     * @return \AirCorsica\XKPlanBundle\Entity\Periode
     */
    public function getPeriode()
    {
        return $this->periode;
    }


    /**
     * Retourne une période au format IATA
     * @param string $annee
     * @param string $saison
     * @return Periode
     */
    static function getDateSaisonIATA($annee,$saison) {
        $datetimeFormat = 'Y/m/d';
        $date = new DateTime();

        if($saison=="hiver"){//hiver
            $dateD = date_create($annee.'-10-31'); //dernier dimanche d'octobre
            $dateD->modify('Last Sunday');
            $dateF = date_create(strval(intval($annee)+1).'-03-31'); //dernier samedi de mars
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

    /**
     * retourne le nom de la saison en fonction d'une date
     *
     * @param DateTime|null $date
     * @return string printemps | ete | automne | hiver
     */
   public static function getSaison(DateTime $date=null){
        $spring = new DateTime('March 20');
        $summer = new DateTime('June 20');
        $fall = new DateTime('September 22');
        $winter = new DateTime('December 21');

        if(null == $date){
            $date = new DateTime();
        }

        switch(true) {
            case $date >= $spring && $date < $summer:
                return 'printemps';
                break;

            case $date >= $summer && $date < $fall:
                return 'ete';
                break;

            case $date >= $fall && $date < $winter:
                return 'automne';
                break;

            default:
                return 'hiver';
        }
   }

    /**
     * @param string $annee yyyy
     * @param string $saison ete | hiver
     * @return string
     */
   public static function getLibelle($annee, $saison){
       $sAnnee = substr($annee,-2);
       if("ete" == $saison){
           $label = "S";
       }else{
           $label = "W";
       }

       return $label.$sAnnee;
   }

}
