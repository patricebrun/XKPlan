<?php
/**
 * Created by PhpStorm.
 * User: JeanJo
 * Date: 13/03/2017
 * Time: 09:45
 */

namespace AirCorsica\XKPlanBundle\Entity\Message;


/**
 * ASMSSMHeader
 */
class ASMSSMHeader
{
    /**
     * type de message
     *
     * @var string $messagetype
     */
    private $messagetype;

    /**
     * echelle de temps
     *
     * @var string $echelletemps
     */
    private $echelletemps;

    /**
     * code action du message
     *
     * @var string $codeaction
     */
    private $codeaction;


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
     * Set echelletemps
     *
     * @param string $echelletemps
     */
    public function setEchelletemps($echelletemps)
    {
        $this->echelletemps = $echelletemps;
    }

    /**
     * Get echelletemps
     *
     * @return string $echelletemps
     */
    public function getEchelletemps()
    {
        return $this->echelletemps;
    }

    /**
     * Set codeaction
     *
     * @param string $codeaction
     */
    public function setCodeaction($codeaction)
    {
        $this->codeaction = $codeaction;
    }

    /**
     * Get codeaction
     *
     * @return string $codeaction
     */
    public function getCodeaction()
    {
        return $this->codeaction;
    }

    /**
     * Render the header to formated text
     *
     * @return string
     */
    public function render()
    {
        /* Demande AirCorsica suite demande partenaire HOP transformer ADM en RPL */
        /*
        $codeActn = $this->codeaction;
        if ($this->codeaction == "ADM"){
            $codeActn = "RPL";
        }
        return $this->messagetype."\n".$this->echelletemps."\n".$codeActn;
        */

        return $this->messagetype."\n".$this->echelletemps."\n".$this->codeaction;
    }

}