<?php
/**
 * Created by PhpStorm.
 * User: JeanJo
 * Date: 13/03/2017
 * Time: 09:45
 */

namespace AirCorsica\XKPlanBundle\Entity\Message;

use AirCorsica\XKPlanBundle\Entity\Message\SCRHeader;
use AirCorsica\XKPlanBundle\Entity\Message\SCRAction;
use AirCorsica\XKPlanBundle\Entity\Message\SCRFooter;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * SCRMessage
 */
class SCRMessage extends AbstractMessage
{

    /**
     * C'est l'id du vol qui a entrainer la génération du message
     *
     * @var  $idVol
     */
    private $idVol;

    /**
     * C'est le libelle du SCRmessage
     *
     * @var $libelle
     */
    private $libelle;

    /**
     * C'est le header du SCRmessage
     *
     * @var SCRHeader $header
     */
    private $header;

    /**
     * C'est les actions du SCRmessage ou data lines
     *
     * @var SCRAction array $action
     */
    private $action;

    /**
     * C'est le footer du SCRmessage
     *
     * @var SCRFooter $footer
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
     * @param SCRHeader $header
     */
    public function setHeader(SCRHeader $header)
    {
        $this->header = $header;
    }

    /**
     * Get header
     *
     * @return SCRHeader
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Add action
     *
     * @param SCRAction $action
     *
     */
    public function addAction(SCRAction $action)
    {
        $this->action[] = $action;
    }

    /**
     * Get action
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set footer
     *
     * @param SCRFooter $footer
     */
    public function setFooter(SCRFooter $footer)
    {
        $this->footer = $footer;
    }

    /**
     * Get footer
     *
     * @return SCRFooter
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
        $temp .= $this->header->render().PHP_EOL;
        foreach ($this->action as $uneaction) {
            $temp .= $uneaction->render().PHP_EOL;
        }

        if('' != $this->footer->render()){
            $temp .= $this->footer->render().PHP_EOL;
        }
        $temp.="SI";

        return $temp;

    }

}