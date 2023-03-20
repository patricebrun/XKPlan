<?php
/**
 * Created by PhpStorm.
 * User: patriceb
 * Date: 12/01/2017
 * Time: 10:49
 */

namespace AirCorsica\XKPlanBundle\Entity\Message;

use Doctrine\Common\Collections\ArrayCollection;

interface InterfaceMessage
{


//    /**
//     * Analyser le vol et en déduire les lots de messages SCR à générer et à distribuer
//     *
//     * @param \AirCorsica\XKPlanBundle\Entity\Vol $vol
//     * @return boolean
//     */
//    public function necessitemessages(\AirCorsica\XKPlanBundle\Entity\Vol $vol);


    /**
     * Génére un(des) message(s)
     *
     * @param array $datamessagetogenerate
     * @return array | boolean
     */
    //public function generer($codeAction,\AirCorsica\XKPlanBundle\Entity\Vol $vol);
    //public function generer($datamessagetogenerate,\AirCorsica\XKPlanBundle\Entity\Vol $vol,\AirCorsica\XKPlanBundle\Entity\VolHistorique $dernierVolDeHistoriqueAEtatConnu);
    public function generer(array $datamessagetogenerate);


}