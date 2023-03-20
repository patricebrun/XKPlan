<?php
/**
 * Created by PhpStorm.
 * User: JeanJo
 * Date: 13/03/2017
 * Time: 09:45
 */

namespace AirCorsica\XKPlanBundle\Entity\Message;


/**
 * SCRHeader
 */
class SCRHeader
{
    /**
     * type de message
     *
     * @var string $messagetype
     */
    private $messagetype;

    /**
     * email de du createur du message
     *
     * @var string $emailoriginator
     */
    private $emailoriginator;

    /**
     * saison IATA concernant le message
     *
     * @var string $IATAseasonconcerned
     */
    private $IATAseasonconcerned;

    /**
     * date de création du message
     *
     * @var string $dateofmessage au format du genre 22APR
     */
    private $dateofmessage;

    /**
     * Aéroport de déchargement à laquelle les créneaux horaires sont demandés
     *
     * @var string $clearanceairportconcerned
     */
    private $clearanceairportconcerned;

    /**
     * Set messagetype
     *
     * @param string $messagetype
     */
    public function setMessagetype($messagetype)
    {
        $this->messagetype = $messagetype;
    }

    /**
     * Get messagetype
     *
     * @return string messagetype
     */
    public function getMessagetype()
    {
        return $this->messagetype;
    }

    /**
     * Set emailoriginator
     *
     * @param string $emailoriginator
     */
    public function setEmailoriginator($emailoriginator)
    {
        $this->emailoriginator = $emailoriginator;
    }

    /**
     * Get emailoriginator
     *
     * @return string emailoriginator
     */
    public function getEmailoriginator()
    {
        return $this->emailoriginator;
    }

    /**
     * Set IATAseasonconcerned
     *
     * @param string $IATAseasonconcerned
     */
    public function setIATAseasonconcerned($IATAseasonconcerned)
    {
        $this->IATAseasonconcerned = $IATAseasonconcerned;
    }

    /**
     * Get IATAseasonconcerned
     *
     * @return string IATAseasonconcerned
     */
    public function getIATAseasonconcerned()
    {
        return $this->IATAseasonconcerned;
    }

    /**
     * Set dateofmessage
     *
     * @param string $dateofmessage
     */
    public function setDateofmessage($dateofmessage)
    {
        $this->dateofmessage = $dateofmessage;
    }

    /**
     * Get dateofmessage
     *
     * @return string $dateofmessage
     */
    public function getDateofmessage()
    {
        return $this->dateofmessage;
    }

    /**
     * Set clearanceairportconcerned
     *
     * @param string $clearanceairportconcerned
     */
    public function setClearanceairportconcerned($clearanceairportconcerned)
    {
        $this->clearanceairportconcerned = $clearanceairportconcerned;
    }

    /**
     * Get clearanceairportconcerned
     *
     * @return string $clearanceairportconcerned
     */
    public function getClearanceairportconcerned()
    {
        return $this->clearanceairportconcerned;
    }

    /**
     * Render the header to formated text
     *
     * @return string
     */
    public function render()
    {
        //Si on colle à la Norme SITA on doit indiqué le mail du createur du message $this->emailoriginator précédé d'un /
        //return $this->messagetype."\r"."/".$this->emailoriginator."\r".$this->IATAseasonconcerned."\r".$this->dateofmessage."\r".$this->clearanceairportconcerned;

        //Dans l'implémentation de Xavier, il n'y est pas

        return implode(PHP_EOL,array_filter(array($this->messagetype,$this->IATAseasonconcerned,$this->dateofmessage,$this->clearanceairportconcerned)));

        //return $this->messagetype."\n".$this->IATAseasonconcerned."\n".$this->dateofmessage."\n".$this->clearanceairportconcerned;
    }

}