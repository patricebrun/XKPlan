<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ModeleSousPeriode
 *
 * @ORM\Table(name="modele_sous_periode")
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\ModeleSousPeriodeRepository")
 */
class ModeleSousPeriode extends AbstractXKPlan
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
     * @var bool
     *
     * @ORM\Column(name="pourPeriodeEstivalle", type="boolean")
     */
    private $pourPeriodeEstivalle = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="pourPeriodeHivernalle", type="boolean")
     */
    private $pourPeriodeHivernalle = 0;


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
     * @return ModeleSousPeriode
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
     * Set pourPeriodeEstivalle
     *
     * @param boolean $pourPeriodeEstivalle
     *
     * @return ModeleSousPeriode
     */
    public function setPourPeriodeEstivalle($pourPeriodeEstivalle)
    {
        $this->pourPeriodeEstivalle = $pourPeriodeEstivalle;

        return $this;
    }

    /**
     * Get pourPeriodeEstivalle
     *
     * @return bool
     */
    public function getPourPeriodeEstivalle()
    {
        return $this->pourPeriodeEstivalle;
    }

    /**
     * Set pourPeriodeHivernalle
     *
     * @param boolean $pourPeriodeHivernalle
     *
     * @return ModeleSousPeriode
     */
    public function setPourPeriodeHivernalle($pourPeriodeHivernalle)
    {
        $this->pourPeriodeHivernalle = $pourPeriodeHivernalle;

        return $this;
    }

    /**
     * Get pourPeriodeHivernalle
     *
     * @return bool
     */
    public function getPourPeriodeHivernalle()
    {
        return $this->pourPeriodeHivernalle;
    }
}

