<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * VolHistorique
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="vol_historique")
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\VolHistoriqueRepository")
 */
class VolHistorique extends Vol
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(type="integer")
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer")
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\VolHistorique")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\VolHistorique", inversedBy="children" , cascade={"persist"})
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="AirCorsica\XKPlanBundle\Entity\VolHistorique", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;

    /**
     * @var Vol
     *
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\Vol" , cascade={"persist"})
     * @ORM\JoinColumn(nullable=true,onDelete="SET NULL")
     */
    protected $volHistorique;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    public function getRoot()
    {
        return $this->root;
    }

    public function setParent(VolHistorique $parent = null)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set lft
     *
     * @param integer $lft
     *
     * @return VolHistorique
     */
    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * Get lft
     *
     * @return integer
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * Set lvl
     *
     * @param integer $lvl
     *
     * @return VolHistorique
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

        return $this;
    }

    /**
     * Get lvl
     *
     * @return integer
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * Set rgt
     *
     * @param integer $rgt
     *
     * @return VolHistorique
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * Get rgt
     *
     * @return integer
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * Set root
     *
     * @param \AirCorsica\XKPlanBundle\Entity\VolHistorique $root
     *
     * @return VolHistorique
     */
    public function setRoot(\AirCorsica\XKPlanBundle\Entity\VolHistorique $root = null)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * Add child
     *
     * @param \AirCorsica\XKPlanBundle\Entity\VolHistorique $child
     *
     * @return VolHistorique
     */
    public function addChild(\AirCorsica\XKPlanBundle\Entity\VolHistorique $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param \AirCorsica\XKPlanBundle\Entity\VolHistorique $child
     */
    public function removeChild(\AirCorsica\XKPlanBundle\Entity\VolHistorique $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }


    /**
     * Contruit un volHistorique depuis un vol
     *
     * @param Vol $vol
     * @param string $type 'update'|'create'
     * @return VolHistorique
     */
    public static function createFromVol(Vol $vol,$type=null,$user=null){
        $destinationVol = new VolHistorique();
        $volReflection = new \ReflectionObject($vol);
        $volProperties = $volReflection->getProperties();

        foreach ($volProperties as $propertie){
            $propertie->setAccessible(true);
            $name = $propertie->getName();
            $value = $propertie->getValue($vol);
            $destinationVol->$name =  $value;
        }

	    $destinationVol->setModificateur($user);
        if('update' != $type){
            $destinationVol->id = null;
            $newPeriodeDeVol = clone $vol->getPeriodeDeVol();
            $destinationVol->setPeriodeDeVol($newPeriodeDeVol);
        }

        $destinationVol->volHistoriqueParentable = null;
        $destinationVol->vol = $vol;

        return $destinationVol;
    }

    /**
     * update depuis un vol
     *
     * @param Vol $vol
     * @return VolHistorique
     */
    public function updateFromVol(Vol $vol){

        $volReflection = new \ReflectionObject($vol);
        $volProperties = $volReflection->getProperties();

        foreach ($volProperties as $propertie){
            $propertie->setAccessible(true);
            $name = $propertie->getName();
            if('periodeDeVol' != $name){
                $value = $propertie->getValue($vol);
                $this->$name =  $value;
            }
        }
        $this->volHistoriqueParentable = null;

        $this->getPeriodeDeVol()->updatePeriode($vol->getPeriodeDeVol());

        return $this;
    }

    /**
     * Set volHistorique
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Vol $volHistorique
     *
     * @return VolHistorique
     */
    public function setVolHistorique(\AirCorsica\XKPlanBundle\Entity\Vol $volHistorique = null)
    {
        $this->volHistorique = $volHistorique;

        return $this;
    }

    /**
     * Get volHistorique
     *
     * @return \AirCorsica\XKPlanBundle\Entity\Vol
     */
    public function getVolHistorique()
    {
        return $this->volHistorique;
    }
}
