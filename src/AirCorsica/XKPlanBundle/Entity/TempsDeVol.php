<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TempsDeVol
 *
 * @ORM\Table(name="temps_de_vol")
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\TempsDeVolRepository")
 */
class TempsDeVol extends AbstractXKPlan
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
     * @var int
     *
     * @ORM\Column(name="duree", type="integer")
     */
    private $duree;

    /**
     * @var Saison
     *
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\PeriodeSaison",  inversedBy="tempsDevol")
     * @ORM\JoinColumn(nullable=true,onDelete="SET NULL")
     */
    private $saison;

    /**
     * @var Ligne
     *
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\Ligne")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ligne;

    /**
     * @var TypeAvion
     *
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\TypeAvion", inversedBy="tempsDeVol")
     * @ORM\JoinColumn(nullable=false)
     */
    private $typeAvion;

    /**
     * transforme la durée minutes en heures, minutes
     *
     * @return array
     */
    public function convertDuree(){
        $time = $this->getDuree();
        $hours = floor($time / 60);
        $minutes = ($time % 60);

        return array('h'=>$hours,'m'=>$minutes);
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
     * Set duree
     *
     * @param integer $duree
     *
     * @return TempsDeVol
     */
    public function setDuree($duree)
    {
        $this->duree = $duree;

        return $this;
    }

    /**
     * Get duree
     *
     * @return int
     */
    public function getDuree()
    {
        return $this->duree;
    }

    /**
     * Set saison
     *
     * @param \AirCorsica\XKPlanBundle\Entity\PeriodeSaison $saison
     *
     * @return TempsDeVol
     */
    public function setSaison(\AirCorsica\XKPlanBundle\Entity\PeriodeSaison $saison)
    {
        $this->saison = $saison;

        return $this;
    }

    /**
     * Get saison
     *
     * @return \AirCorsica\XKPlanBundle\Entity\PeriodeSaison
     */
    public function getSaison()
    {
        return $this->saison;
    }

    /**
     * Set ligne
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Ligne $ligne
     *
     * @return TempsDeVol
     */
    public function setLigne(\AirCorsica\XKPlanBundle\Entity\Ligne $ligne)
    {
        $this->ligne = $ligne;

        return $this;
    }

    /**
     * Get ligne
     *
     * @return \AirCorsica\XKPlanBundle\Entity\Ligne
     */
    public function getLigne()
    {
        return $this->ligne;
    }

    /**
     * Set typeAvion
     *
     * @param \AirCorsica\XKPlanBundle\Entity\TypeAvion $typeAvion
     *
     * @return TempsDeVol
     */
    public function setTypeAvion(\AirCorsica\XKPlanBundle\Entity\TypeAvion $typeAvion)
    {
        $this->typeAvion = $typeAvion;

        return $this;
    }

    /**
     * Get typeAvion
     *
     * @return \AirCorsica\XKPlanBundle\Entity\TypeAvion
     */
    public function getTypeAvion()
    {
        return $this->typeAvion;
    }
}
