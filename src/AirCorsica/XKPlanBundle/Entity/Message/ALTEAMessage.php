<?php
/**
 * Created by PhpStorm.
 * User: JeanJo
 * Date: 28/03/2017
 * Time: 14:54
 */

namespace AirCorsica\XKPlanBundle\Entity\Message;

use Symfony\Component\Validator\Constraints\DateTime;

/**
 * ALTEAMessage
 */
class ALTEAMessage
{

    private $idalteamessage;

    private $indexdegenerationl;

    private $incrementnumeromessagerieSITA;

    private $idvol;

    private $codeemetteurSITA;

    private $adestinataires;

    private $aemailsdestinataires;

    private $messagelibelle;

    private $messagetexte;

    private $typemessage;

    /**
     * Get idalteamessage
     *
     * @return $idalteamessage
     */
    public function getIdalteamessage()
    {
        return $this->idalteamessage;
    }

    /**
     * Set incrementnumeromessagerieSITA
     *
     * @param array $incrementnumeromessagerieSITA
     */
    public function setIncrementnumeromessagerieSITA($incrementnumeromessagerieSITA)
    {
        $this->incrementnumeromessagerieSITA = $incrementnumeromessagerieSITA;
    }

    /**
     * Get adestinataires
     *
     * @return $adestinataires
     */
    public function getAdestinataires()
    {
        return $this->adestinataires;
    }

    /**
     * Set adestinataires
     *
     * @param array $adestinataires
     */
    public function setAdestinataires($adestinataires)
    {
        $this->adestinataires = $adestinataires;
    }

    /**
     * Get aemailsdestinataires
     *
     * @return $aemailsdestinataires
     */
    public function getAemailsdestinataires()
    {
        return $this->aemailsdestinataires;
    }

    /**
     * Set adestinataires
     *
     * @param array $adestinataires
     */
    public function setAemailsdestinataires($aemailsdestinataires)
    {
        $this->aemailsdestinataires = $aemailsdestinataires;
    }

    /**
     * Get typemessage
     *
     * @return $typemessage
     */
    public function getTypemessage()
    {
        $temp =explode(':',$this->messagelibelle);
        return $temp[0];
    }

    /**
     * Get idvol
     *
     * @return $idvol
     */
    public function getIdvol()
    {
        return $this->idvol;
    }

    /**
     * Get messagelibelle
     *
     * @return $messagelibelle
     */
    public function getMessagelibelle()
    {
        return $this->messagelibelle;
    }

    /**
     * Get messagetexte
     *
     * @return $$messagetexte
     */
    public function getMessagetexte()
    {
        return $this->messagetexte;
    }

    /**
     * Set messagetexte
     *
     * @param string $messagetexte
     */
    public function setMessagetexte($messagetexte)
    {
        $this->messagetexte = $messagetexte;
    }

    /**
     * ALTEAMessage constructor.
     * @param int $idUniqueALTEAMessage;
     * @param int $indexdegenerationl;
     * @param int $idvol
     * @param int $incrementnumeromessagerieSITA
     * @param string $codeemetteurSITA
     * @param array() $adestinataires
     * @param string $messagetexte
     */
    function __construct($idUniqueALTEAMessage,$indexdegenerationl,$incrementnumeromessagerieSITA,$idvol,$codeemetteurSITA,$adestinataires,$messagetexte,$messagelibelle)
    {
        //id de ce message (c'est un timestamp pour qu'il soit unique)
        $this->idalteamessage = $idUniqueALTEAMessage;

        //l'index de génération de ce message
        $this->indexdegenerationl = $indexdegenerationl;

        //l'id de l'increment de la messagerie SITA pour ce lot de messages (dans la BDD de Xavier, table variable, id=3, NUMEROMESSAGERIESITA)
        $this->incrementnumeromessagerieSITA = $incrementnumeromessagerieSITA;

        //l'id BDD du vol
        $this->idvol = $idvol;

        //le code emetteur du message (dans la BDD de Xavier, table variable, id=2, CODEEMETTEURSITA)
        $this->codeemetteurSITA = $codeemetteurSITA;

        //les destinataires array d'adresse SITA
        $this->adestinataires = $adestinataires;

        //contenu du message
        $this->messagetexte = $messagetexte;

        //libelle du message
        $this->messagelibelle = $messagelibelle;

    }

    public function getFileName(){

        $datetime = new \DateTime();
        $formatedName = "";
        $formatedName .= "MSG_".$datetime->format('Ymd')."_VOL_".$this->idvol."_MSG_".$this->indexdegenerationl.".SND";

        return $formatedName;
    }

    public function didExistAlteaDestinatairesAvecAdresseSITA(){
        $result = true;
        $nbDeDestinataireAdresseSITA = 0;
        foreach( $this->adestinataires as $key => $unDestinataire) {
            if($unDestinataire['adresse'] != ""){
                $nbDeDestinataireAdresseSITA++;
            }
        }
        if($nbDeDestinataireAdresseSITA==0){
            $result = false;
        }
        return $result;
    }

    public function removeUnDestinataire($adresseSITAdestinatairetocancel){

        $arrayDestinataireNew = $this->adestinataires;

        $email = substr($adresseSITAdestinatairetocancel, strpos($adresseSITAdestinatairetocancel, "#") + 1);
        $adress = substr($adresseSITAdestinatairetocancel, 0, - (strlen($email)+1));

        foreach( $arrayDestinataireNew as $key => $unDestinataire){
            //if( ($unDestinataire['adresse'] == $adresseSITAdestinatairetocancel) || ($unDestinataire['email'] == $adresseSITAdestinatairetocancel) ){
            if( ($unDestinataire['adresse'] == $adress) && ($unDestinataire['email'] == $email) ){
                unset($arrayDestinataireNew[$key]);
            }
        }

        $this->setAdestinataires($arrayDestinataireNew);
    }

    public function addUnDestinataire($adresseSITAdestinatairetoadd){

        $arrayDestinataireNew = $this->adestinataires;

        $dataTmp = explode("#",$adresseSITAdestinatairetoadd);
        if($dataTmp[0] == " "){
            $dataTmp[0] = ""; //adresseSITA
        }
        if($dataTmp[1] == " "){
            $dataTmp[1] = ""; //emailSITA
        }
        if($dataTmp[2] == " "){
            $dataTmp[2] = ""; //libelleSITA
        }
        if($dataTmp[3] == " "){
            $dataTmp[3] = ""; //groupeSITA
        }
        if($dataTmp[4] == " "){
            $dataTmp[4] = ""; //payscoordinateurSITA
        }

        array_push($arrayDestinataireNew, array('adresse' => $dataTmp[0], 'email' => $dataTmp[1], 'groupe' => $dataTmp[3], 'libelle' => $dataTmp[2], 'coordinateur' => $dataTmp[4]));

        $this->setAdestinataires($arrayDestinataireNew);
    }


    /**
     * Render the ALTEA message for sending
     *
     * @return string
     */
    public function render()
    {
        $renderingResult = "";

        //La partie destinataire
        $renderingResult .= "=DESTINATION TYPE B"."\n";
        foreach ($this->adestinataires as $undestinataire) {
            if( ($undestinataire['adresse'] != "") ){//cas d'une adresse SITA (évite d'ajouter les adresse email)
                $renderingResult .= "STX," . $undestinataire['adresse'] . "\n";
            }
        }

        //La partie origine du message
        $renderingResult .= "=ORIGIN"."\n";
        $renderingResult .= $this->codeemetteurSITA."\n";

        //La partie message id
        $renderingResult .= "=MSGID"."\n";
        $renderingResult .= strval($this->incrementnumeromessagerieSITA+1)."\n";

        //La partie texte du message
        $renderingResult .= "=TEXT"."\n";
        $renderingResult .= $this->messagetexte;

        return $renderingResult;
    }


    /**
     * Render the ALTEA message for Email sending
     *
     * @return string
     */
    public function renderForEmail()
    {
        return $this->messagetexte;
    }



}
