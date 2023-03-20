<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Avion
 *
 * @ORM\Table(name="avion")
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\AvionRepository")
 */
class Avion extends AbstractXKPlan
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
     * @Assert\NotBlank(message = "Le champ Nom est obligatoire")
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var bool
     *
     * @ORM\Column(name="affrete", type="boolean")
     */
    private $affrete;

    /**
     * @var int
     * @Assert\Range(
     *      min = 0,
     *      max = 9999,
     *      minMessage = "La valeur minimum du champ Ordre doit être : {{ limit }}",
     *      maxMessage = "La valeur maximum du champ Ordre doit être : {{ limit }}",
     * )
     * @ORM\Column(name="ordre", type="smallint")
     */
    private $ordre;

    /**
     * @var TypeAvion
     * @Assert\NotNull(message = "Le champ Type Avion est obligatoire")
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\TypeAvion")
     * @ORM\JoinColumn(nullable=false)
     */
    private $typeAvion;

    /**
     * @var Compagnie
     * @Assert\NotNull(message = "Le champ Compagnie est obligatoire")
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\Compagnie")
     * @ORM\JoinColumn(nullable=false)
     */
    private $compagnie;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AirCorsica\XKPlanBundle\Entity\PeriodeImmobilisation",  mappedBy="avion", cascade={"persist", "remove"})
     * @ORM\OrderBy({"dateDebut" = "ASC","dateFin" = "ASC"})
     */
    private $periodesImmobilsation;

    /**
     * Avion constructor.
     */
    public function __construct()
    {
        $this->periodesImmobilsation = new ArrayCollection();
    }

    /**
     * toString
     * @return string
     */
    public function __toString() {
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
     * @return Avion
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
     * Set affrete
     *
     * @param boolean $affrete
     *
     * @return Avion
     */
    public function setAffrete($affrete)
    {
        $this->affrete = $affrete;

        return $this;
    }

    /**
     * Get affrete
     *
     * @return bool
     */
    public function getAffrete()
    {
        return $this->affrete;
    }

    /**
     * Set ordre
     *
     * @param integer $ordre
     *
     * @return Avion
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre
     *
     * @return int
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * Set typeAvion
     *
     * @param \AirCorsica\XKPlanBundle\Entity\TypeAvion $typeAvion
     *
     * @return Avion
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

    /**
     * Add periodesImmobilsation
     *
     * @param \AirCorsica\XKPlanBundle\Entity\PeriodeImmobilisation $periodesImmobilsation
     *
     * @return Avion
     */
    public function addPeriodesImmobilsation(\AirCorsica\XKPlanBundle\Entity\PeriodeImmobilisation $periodesImmobilsation)
    {
        //$this->periodesImmobilsation[] = $periodesImmobilsation;

        $periodesImmobilsation->setAvion($this);
        $this->periodesImmobilsation->add($periodesImmobilsation);

        return $this;
    }

    /**
     * Remove periodesImmobilsation
     *
     * @param \AirCorsica\XKPlanBundle\Entity\PeriodeImmobilisation $periodesImmobilsation
     */
    public function removePeriodesImmobilsation(\AirCorsica\XKPlanBundle\Entity\PeriodeImmobilisation $periodesImmobilsation)
    {
        $periodesImmobilsation->setAvion(null);
        $this->periodesImmobilsation->removeElement($periodesImmobilsation);
    }

    /**
     * Get periodesImmobilsation
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPeriodesImmobilsation()
    {
        return $this->periodesImmobilsation;
    }
    public function getPeriodesImmobilsationWithTemplate(EntityManager $em,Template $template)
    {
        $repo = $em->getRepository('AirCorsicaXKPlanBundle:PeriodeImmobilisation');
        return $repo->getImmoForAvionAndTemplate($this,$template);
    }




    /**
     * Set compagnie
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Compagnie $compagnie
     *
     * @return Avion
     */
    public function setCompagnie(\AirCorsica\XKPlanBundle\Entity\Compagnie $compagnie)
    {
        $this->compagnie = $compagnie;

        return $this;
    }

    /**
     * Get compagnie
     *
     * @return \AirCorsica\XKPlanBundle\Entity\Compagnie
     */
    public function getCompagnie()
    {
        return $this->compagnie;
    }
}
