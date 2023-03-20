<?php
/**
 * Created by PhpStorm.
 * User: patriceb
 * Date: 07/12/2016
 * Time: 11:07
 */

namespace AirCorsica\XKPlanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class AbstractXKPlan
 * @package AirCorsica\XKPlanBundle\Entity
 * @ORM\MappedSuperclass
 */
abstract class AbstractXKPlan
{
    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="dateTimeModification", type="datetime")
     */
    protected $dateTimeModification;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="dateTimeCreation" ,type="datetime")
     */
    protected $dateTimeCreation;

    /**
     * @var Utilisateur
     *
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\Utilisateur")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $createur;

    /**
     * @var Utilisateur
     *
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\Utilisateur")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $modificateur;


    /**
     * Set modificateur
     *
     * @param Utilisateur $modificateur
     *
     * @return AbstractXKPlan
     */
    public function setModificateur($modificateur)
    {
        $this->modificateur = $modificateur;

        return $this;
    }

    /**
     * Get modificateur
     *
     * @return Utilisateur
     */
    public function getModificateur()
    {
        return $this->modificateur;
    }

    /**
     * Set createur
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Utilisateur $createur
     *
     * @return CodeSharePrecharge
     */
    public function setCreateur(\AirCorsica\XKPlanBundle\Entity\Utilisateur $createur)
    {
        $this->createur = $createur;

        return $this;
    }

    /**
     * Get dateTimeModification
     *
     * @return \DateTime
     */
    public function getDateTimeModification()
    {
        return $this->dateTimeModification;
    }

    /**
     * Set dateTimeModification
     *
     * @param \DateTime $dateTimeModification
     *
     * @return AbstractXKPlan
     */
    public function setDateTimeModification(\DateTime  $dateTimeModification)
    {
        $this->dateTimeModification = $dateTimeModification;

        return $this;
    }

    /**
     * Get dateTimeCreation
     *
     * @return \DateTime
     */
    public function getDateTimeCreation()
    {
        return $this->dateTimeCreation;
    }

    /**
     * Set dateTimeCreation
     *
     * @param \DateTime $dateTimeCreation
     *
     * @return AbstractXKPlan
     */
    public function setDateTimeCreation($dateTimeCreation)
    {
        $this->dateTimeCreation = $dateTimeCreation;

        return $this;
    }
}