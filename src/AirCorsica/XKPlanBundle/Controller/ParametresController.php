<?php

namespace AirCorsica\XKPlanBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ParametresController extends Controller
{
    /**
     * * Index des paramètres globaux
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction(Request $request)
    {
        $path = getcwd().'/../src/AirCorsica/XKPlanBundle/Resources/config/parametres_globaux.yml';

        $csrfToken = $this->has('security.csrf.token_manager')
            ? $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue()
            : null;

        $parametres = Yaml::parse(file_get_contents($path));

        foreach($parametres as $i => $parametre)
        {
            $messagereplyaddress = $parametre['messagereplyaddress'];
            $codeaeroportdattacheaircorsica = $parametre['codeaeroportdattacheaircorsica'];
            $codeemetteursita = $parametre['codeemetteursita'];
            $adressesitaaltea = $parametre['adressesitaaltea'];
            $emailsitaaltea = $parametre['emailsitaaltea'];
            $emailsitassim7 = $parametre['emailsitassim7'];
        }

        return $this->render('AirCorsicaXKPlanBundle:Parametres:index.html.twig', array(
            'request'               => $request,
            'csrf_token'            =>$csrfToken,
            'messagereplyaddress'   => $messagereplyaddress,
            'codeaeroportdattacheaircorsica'   => $codeaeroportdattacheaircorsica,
            'codeemetteursita'   => $codeemetteursita,
            'adressesitaaltea'   => $adressesitaaltea,
            'emailsitaaltea'   => $emailsitaaltea,
            'emailsitassim7'   => $emailsitassim7
        ));
    }

    /**
     * * Enregistrement des paramètres globaux
     * @Security("has_role('ROLE_ADMIN')")
     * @return JsonResponse
     */
    public function saveParametresAction(Request $request)
    {

        $path = getcwd().'/../src/AirCorsica/XKPlanBundle/Resources/config/parametres_globaux.yml';

        $parametres_messagereplyaddress = $request->get('parametres_messagereplyaddress');
        $parametres_codeaeroportdattacheaircorsica = $request->get('parametres_codeaeroportdattacheaircorsica');
        $parametres_codeemetteursita = $request->get('parametres_codeemetteursita');
        $parametres_adressesitaaltea = $request->get('parametres_adressesitaaltea');
        $parametres_emailsitaaltea = $request->get('parametres_emailsitaaltea');
        $parametres_emailsitassim7 = $request->get('parametres_emailsitassim7');

        $array = array(
            'parameters' => array(
                'messagereplyaddress' => $parametres_messagereplyaddress,
                'codeaeroportdattacheaircorsica' => $parametres_codeaeroportdattacheaircorsica,
                'codeemetteursita' => $parametres_codeemetteursita,
                'adressesitaaltea' => $parametres_adressesitaaltea,
                'emailsitaaltea' => $parametres_emailsitaaltea,
                'emailsitassim7' => $parametres_emailsitassim7
            ),
        );

        $yaml = Yaml::dump($array);

        file_put_contents($path, $yaml);

        $response = new JsonResponse();

        $response->setData(array(
            'valide' => true,
        ));

        $request->getSession()->getFlashBag()->add('success', 'Paramètres mis à jour avec succès.');

        return $response;
    }

}
