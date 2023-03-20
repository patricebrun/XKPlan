<?php
/**
 * Created by PhpStorm.
 * User: JeanJo
 * Date: 13/03/2017
 * Time: 09:45
 */

namespace AirCorsica\XKPlanBundle\Entity\Message;

use AirCorsica\XKPlanBundle\Entity\Message\ASMSSMHeader;
use AirCorsica\XKPlanBundle\Entity\Message\ASMSSMAction;
use AirCorsica\XKPlanBundle\Entity\Message\ASMSSMFooter;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * ASMSSMMessage
 */
class ASMSSMMessage extends AbstractMessage
{

    /**
     * C'est l'id du vol qui a entrainer la génération du message
     *
     * @var  $idVol
     */
    private $idVol;

    /**
     * C'est le libelle du ASMSSMMessage
     *
     * @var $libelle
     */
    private $libelle;

    /**
     * C'est le header du ASMSSMMessage
     *
     * @var ASMSSMHeader $header
     */
    private $header;

    /**
     * C'est l'action du ASMSSMMessage ou data line
     *
     * @var ASMSSMAction $action
     */
    private $action;

    /**
     * C'est le footer du ASMSSMMessage
     *
     * @var ASMSSMFooter $footer
     */
    private $footer;

    /**
     * Set idVol
     *
     * @param $idVol
     */
    public function setIdVol($idVol)
    {
        $this->idVol = $idVol;
    }

    /**
     * Get idVol
     *
     * @return $idVol
     */
    public function getIdVol()
    {
        return $this->idVol;
    }

    /**
     * Set libelle
     *
     * @param $libelle
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;
    }

    /**
     * Get libelle
     *
     * @return $libelle
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set header
     *
     * @param ASMSSMHeader $header
     */
    public function setHeader(ASMSSMHeader $header)
    {
        $this->header = $header;
    }

    /**
     * Get header
     *
     * @return ASMSSMHeader
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Set action
     *
     * @param ASMSSMAction $action
     *
     */
    public function setAction(ASMSSMAction $action)
    {
        $this->action = $action;
    }

    /**
     * Get action
     *
     * @return ASMSSMAction
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set footer
     *
     * @param ASMSSMFooter $footer
     */
    public function setFooter(ASMSSMFooter $footer)
    {
        $this->footer = $footer;
    }

    /**
     * Get footer
     *
     * @return ASMSSMFooter
     */
    public function getFooter()
    {
        return $this->footer;
    }

    public function __construct()
    {
        $this->action = new ArrayCollection();
    }

    public function render(){

        $temp="";
        $temp .= $this->header->render()."\n";
        $temp .= $this->action->render()."\n";
        if($this->action->getCodeAction() != "CNL") {
            //Correction bug: Altea n'interprète pas le footer dans le cas d'une action DELETE
            $temp .= $this->footer->render();
        }

        return $temp;

    }

}