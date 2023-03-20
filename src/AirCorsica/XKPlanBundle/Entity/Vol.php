<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\DiscriminatorMap;

/**
 * Vol
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @DiscriminatorMap({"vol" = "Vol", "volhistorique" = "VolHistorique"})
 * @ORM\Table(name="vol")
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\VolRepository")
 */
class Vol extends AbstractXKPlan
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
     * @var string
     *
     * @ORM\Column(name="numero", type="string", length=255)
     */
    protected $numero;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="text", nullable=true)
     */
    protected $commentaire;

    /**
     * @var Avion
     *
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\Avion")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $avion;

    /**
     * @var Compagnie
     *
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\Compagnie")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $compagnie;

    /**
     * @var TypeDeVol
     *
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\TypeDeVol")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $typeDeVol;

    /**
     * @var Affretement
     *
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\Affretement")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $affretement;

    /**
     * @var NatureDeVol
     *
     * @ORM\ManyToMany(targetEntity="AirCorsica\XKPlanBundle\Entity\NatureDeVol")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $naturesDeVol;

    /**
     * @var Template
     *
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\Template", inversedBy="vols")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $template;

    /**
     * @var PeriodeDeVol
     *
     * @ORM\OneToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\PeriodeDeVol", inversedBy="vol", cascade={"persist", "remove"})
     *
     */
    protected $periodeDeVol;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AirCorsica\XKPlanBundle\Entity\CodeShareVol", inversedBy="vols", cascade={"persist", "remove"})
     *
     */
    protected $codesShareVol;


    /**
     * @var VolHistorique
     *
     * @ORM\OneToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\VolHistorique")
     * @ORM\JoinColumn(name="historique_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $volHistoriqueParentable;

    /**
     * @var Ligne
     *
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\Ligne")
     */
    protected $ligne;

    public function __construct()
    {
        $this->naturesDeVol = new ArrayCollection();
        $this->codesShareVol = new ArrayCollection();
    }

    /**
     * toString
     * @return string
     */
    public function __toString() {
        return $this->getNumero();
    }


    function __clone()
    {
        $this->periodeDeVol   = clone $this->periodeDeVol;
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
     * Set id
     *
     * @param int $id
     *
     * @return Vol
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set numero
     *
     * @param string $numero
     *
     * @return Vol
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * Get numero
     *
     * @return string
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set commentaire
     *
     * @param string $commentaire
     *
     * @return Vol
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get commentaire
     *
     * @return string
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }

    /**
     * Set avion
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Avion $avion
     *
     * @return Vol
     */
    public function setAvion(\AirCorsica\XKPlanBundle\Entity\Avion $avion)
    {
        $this->avion = $avion;

        return $this;
    }

    /**
     * Get avion
     *
     * @return \AirCorsica\XKPlanBundle\Entity\Avion
     */
    public function getAvion()
    {
        return $this->avion;
    }

    /**
     * Set compagnie
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Compagnie $compagnie
     *
     * @return Vol
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

    /**
     * Set typeDeVol
     *
     * @param \AirCorsica\XKPlanBundle\Entity\TypeDeVol $typeDeVol
     *
     * @return Vol
     */
    public function setTypeDeVol(\AirCorsica\XKPlanBundle\Entity\TypeDeVol $typeDeVol)
    {
        $this->typeDeVol = $typeDeVol;

        return $this;
    }

    /**
     * Get typeDeVol
     *
     * @return \AirCorsica\XKPlanBundle\Entity\TypeDeVol
     */
    public function getTypeDeVol()
    {
        return $this->typeDeVol;
    }

    /**
     * Set affretement
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Affretement $affretement
     *
     * @return Vol
     */
    public function setAffretement(\AirCorsica\XKPlanBundle\Entity\Affretement $affretement = null)
    {
        $this->affretement = $affretement;

        return $this;
    }

    /**
     * Get affretement
     *
     * @return \AirCorsica\XKPlanBundle\Entity\Affretement
     */
    public function getAffretement()
    {
        return $this->affretement;
    }

    /**
     * Add naturesDeVol
     *
     * @param \AirCorsica\XKPlanBundle\Entity\NatureDeVol $naturesDeVol
     *
     * @return Vol
     */
    public function addNaturesDeVol(\AirCorsica\XKPlanBundle\Entity\NatureDeVol $naturesDeVol)
    {
        $this->naturesDeVol[] = $naturesDeVol;

        return $this;
    }

    /**
     * Remove naturesDeVol
     *
     * @param \AirCorsica\XKPlanBundle\Entity\NatureDeVol $naturesDeVol
     */
    public function removeNaturesDeVol(\AirCorsica\XKPlanBundle\Entity\NatureDeVol $naturesDeVol)
    {
        $this->naturesDeVol->removeElement($naturesDeVol);
    }

    /**
     * Get naturesDeVol
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNaturesDeVol()
    {
        return $this->naturesDeVol;
    }

    /**
     * Set ligne
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Ligne $ligne
     *
     * @return Vol
     */
    public function setLigne(\AirCorsica\XKPlanBundle\Entity\Ligne $ligne = null)
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
     * Set createur
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Utilisateur $createur
     *
     * @return Vol
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
     * Set periodeDeVol
     *
     * @param \AirCorsica\XKPlanBundle\Entity\PeriodeDeVol $periodeDeVol
     *
     * @return Vol
     */
    public function setPeriodeDeVol(\AirCorsica\XKPlanBundle\Entity\PeriodeDeVol $periodeDeVol = null)
    {
        $this->periodeDeVol = $periodeDeVol;

        return $this;
    }

    /**
     * Get periodeDeVol
     *
     * @return \AirCorsica\XKPlanBundle\Entity\PeriodeDeVol
     */
    public function getPeriodeDeVol()
    {
        return $this->periodeDeVol;
    }


    /**
     * Set la propriété codesShareVol depuis une liste de code (string)
     * @param array $aListCodesShare
     * @return Vol
     */
    public function setCodesShareFromList(array $aListCodesShare){
        $this->codesShareVol->clear();
        foreach ($aListCodesShare as $sCodeShareVol){
            $oCodeshareVol = new CodeShareVol();
            $oCodeshareVol->setLibelle($sCodeShareVol);
            $this->addCodesShareVol($oCodeshareVol);
        }

        return $this;
    }

    /**
     * Modifie les dates de la periode
     * @param Periode $periode
     * @return Vol $this
     */
    public function updatePeriodeDeVol(Periode $periode){
        $this->periodeDeVol->setDateDebut($periode->getDateDebut());
        $this->periodeDeVol->setDateFin($periode->getDateFin());
        return $this;
    }


    /**
     * Add codesShareVol
     *
     * @param \AirCorsica\XKPlanBundle\Entity\CodeShareVol $codesShareVol
     *
     * @return Vol
     */
    public function addCodesShareVol(\AirCorsica\XKPlanBundle\Entity\CodeShareVol $codesShareVol)
    {
        $codesShareVol->addVol($this);
        $this->codesShareVol[] = $codesShareVol;

        return $this;
    }

    /**
     * Remove codesShareVol
     *
     * @param \AirCorsica\XKPlanBundle\Entity\CodeShareVol $codesShareVol
     */
    public function removeCodesShareVol(\AirCorsica\XKPlanBundle\Entity\CodeShareVol $codesShareVol)
    {
        $this->codesShareVol->removeElement($codesShareVol);
    }

    /**
     * Get codesShareVol
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCodesShareVol()
    {
        return $this->codesShareVol;
    }

    /**
     * Set template
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Template $template
     *
     * @return Vol
     */
    public function setTemplate(\AirCorsica\XKPlanBundle\Entity\Template $template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template
     *
     * @return \AirCorsica\XKPlanBundle\Entity\Template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set volHistoriqueParentable
     *
     * @param \AirCorsica\XKPlanBundle\Entity\VolHistorique $volHistoriqueParentable
     *
     * @return Vol
     */
    public function setVolHistoriqueParentable(\AirCorsica\XKPlanBundle\Entity\VolHistorique $volHistoriqueParentable = null)
    {
        $this->volHistoriqueParentable = $volHistoriqueParentable;

        return $this;
    }

    /**
     * Get volHistoriqueParentable
     *
     * @return \AirCorsica\XKPlanBundle\Entity\VolHistorique
     */
    public function getVolHistoriqueParentable()
    {
        return $this->volHistoriqueParentable;
    }

    /**
     * calcule le temps de vol
     *
     * @return bool|string
     */
    public function getTempsDeVol(){
        if($this->getPeriodeDeVol()->getDecollage() && $this->getPeriodeDeVol()->getAtterissage()){
            $interval = $this->getPeriodeDeVol()->getDecollage()->diff($this->getPeriodeDeVol()->getAtterissage());

            return $interval->format('%hh %im');
        }else{
            /**
             * @todo lever une exception
             */
            return false;
        }
    }

    /**
     * Remonte la sauvegarde d'un vol
     *
     * @param VolHistorique $volHistorique
     * @return Vol
     */
    public function backUpFromVolHistorique(VolHistorique $volHistorique){
        $volReflection = new \ReflectionObject($volHistorique);
        $volProperties = $volReflection->getProperties();

        foreach ($volProperties as $propertie){

            $propertie->setAccessible(true);
            $name = $propertie->getName();
            if(property_exists($this,$name) && 'id' != $name){
                $value = $propertie->getValue($volHistorique);

                if("periodeDeVol" == $name){
                    $this->getPeriodeDeVol()->updatePeriode($value);

                }else{
                    $this->$name =  $value;
                }
            }

        }

        $this->setVolHistoriqueParentable($volHistorique);

        return $this;
    }

    /**
     * delestage ponctuel
     *
     * @param \DateTimeImmutable $date
     * @return Vol $this
     */
    public function delestagePonctuel(\DateTimeImmutable $date, $em){

        //création du nouveau vol délésté pour une période de 1 jour
        $volDeleste = clone $this;
        $periodeVolDeleste = new PeriodeDeVol(
            $date->format("Y-m-d"),
            $date->format("Y-m-d"),
            $this->getPeriodeDeVol()->getDecollage()->format('H:i:s'),
            $this->getPeriodeDeVol()->getAtterissage()->format('H:i:s'),
            PeriodeDeVol::joursToValuesNoIndex(array($date->format("w"))),
            'pendingCancel'
        );
        $periodeVolDeleste->setDeleste(true);
        $volDeleste->setPeriodeDeVol($periodeVolDeleste);
        $volDeleste->addVolHistorique($em);
        $em->persist($volDeleste);

        //création du vol suivant le déléstage ponctuel
        $volAfterDeleste = clone $this;
        $periodeVolAfterDeleste = new PeriodeDeVol(
            $date->add(new \DateInterval("P1D"))->format("Y-m-d"),
            $this->getPeriodeDeVol()->getDateFin()->format("Y-m-d"),
            $this->getPeriodeDeVol()->getDecollage()->format('H:i:s'),
            $this->getPeriodeDeVol()->getAtterissage()->format('H:i:s'),
            $this->getPeriodeDeVol()->getJoursDeValidite(),
            $this->getPeriodeDeVol()->getEtat()
        );
        $volAfterDeleste->setPeriodeDeVol($periodeVolAfterDeleste);
        $volAfterDeleste->addVolHistorique($em);
        $em->persist($volAfterDeleste);

        //on raccourcit la période d'origine
        $this->getPeriodeDeVol()->setDateFin($date->sub(new \DateInterval("P1D")));
        //idem pour le vol historique
        $this->getVolHistoriqueParentable()->getPeriodeDeVol()->setDateFin($date->sub(new \DateInterval("P1D")));
        $em->persist($this);

        return $this;
    }

    /**
     * delestage periode du vol
     *
     * @return Vol $this
     */
    public function delestagePeriode(){
        $this->getPeriodeDeVol()->setDeleste(true);
        $this->getPeriodeDeVol()->setEtat('pendingCancel');
        $this->getVolHistoriqueParentable()->getPeriodeDeVol()->setDeleste(true);
        $this->getVolHistoriqueParentable()->getPeriodeDeVol()->setEtat('pendingCancel');

        return $this;
    }


    /**
     * Ajoute un vol historique pour le vol courant
     *
     * @param $em
     */
    public function addVolHistorique($em){
        $volHistoriqueNew = VolHistorique::createFromVol($this);
        $this->saveVolInHistorique($volHistoriqueNew, $em);
        $this->setVolHistoriqueParentable($volHistoriqueNew);
        $volHistoriqueNew->setVolHistorique($this);
    }


    /**
     * Ajoute un volHistorique et le positionne dans l'abre historique
     * Attention pas de flush dans cette méthode, afin de persiter les données faire un flush après l'appel de cette méthode
     *
     * @param VolHistorique|array $volHistorique
     * @param VolHistorique $volParent
     * @return void
     */
    public static function saveVolInHistorique($volHistorique,$em,$volParent = null)
    {
        if($volParent){
            self::insertInTree($volHistorique, $volParent, $em);
        }
        $em->persist($volHistorique);

        return;
    }

    /**
     * @param Vol $vol
     * @param Vol $volParent
     * @param $em
     * @return bool
     */
    public static function insertInTree(Vol $vol, Vol $volParent,$em)
    {
        $vol->setParent($volParent);
        $repo = $em->getRepository('AirCorsicaXKPlanBundle:VolHistorique');

        if($repo->verify()){
            $repo->recover();

            return true;
        }else{
            /**
             * @todo traiter les erreurs
             */



            return false;
        }
    }


}
