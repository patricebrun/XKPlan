<?php
/**
 * Created by PhpStorm.
 * User: damie
 * Date: 07/02/2017
 * Time: 16:37
 */

namespace AirCorsica\XKPlanBundle\Entity\Message;



class AbstractMessage
{

    private $destinataires;

    public function getDestinataires(){
        return $this->destinataires;
    }

    public function addDestinataires($destinataire){
        $this->destinataires[] = $destinataire;
    }

    public function envoyer(){
    //TODO
    }
}