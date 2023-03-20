<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Utilisateur controller.
 *
 */
class UtilisateurController extends Controller
{
    /**
     * Lists all Utilisateur entities.
     *
     */
    public function indexAction(Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();

        return $this->render('AirCorsicaXKPlanBundle:Utilisateur:index.html.twig', array(
            'users' => $users,
            'request'   => $request,
        ));
    }

    /**
     * Displays a form to edit an existing Utilisateur entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, Utilisateur $user)
    {
        $editForm = $this->createForm('AirCorsica\XKPlanBundle\Form\UtilisateurType', $user);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            /* Permet de mettre à jour les données user, dont le mot de passe*/
            $userManager = $this->container->get('fos_user.user_manager');
            $userManager->updateUser($user);
            /* Permet de mettre à jour les données user, dont le mot de passe*/

            $request->getSession()->getFlashBag()->add('success', 'Utilisateur modifié avec succès.');

            return $this->redirectToRoute('utilisateur_edit', array('id' => $user->getId()));
        }

        return $this->render('AirCorsicaXKPlanBundle:Utilisateur:edit.html.twig', array(
            'user' => $user,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Deletes a Utilisateur entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, Utilisateur $user)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('AirCorsicaXKPlanBundle:Utilisateur')->find($user);

        if (!$user) {
            throw $this->createNotFoundException('Cet utilisateur n\'existe pas');
        }
        $em->remove($user);
        $em->flush($user);

        $request->getSession()->getFlashBag()->add('success', 'Utilisateur supprimé avec succès.');

        return $this->redirectToRoute('utilisateur_index');
    }

    /**
     * suppression en masse entities.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deletemasseAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoUtilisateur = $em->getRepository('AirCorsicaXKPlanBundle:Utilisateur');
        $aIdUtilisateurs = explode('_',$request->get('ids_item'));
        foreach ($aIdUtilisateurs as $aIdUtilisateur) {
            $utilisateur = $repoUtilisateur->find($aIdUtilisateur);
            $em->remove($utilisateur);
        }
        $this->getDoctrine()->getManager()->flush();

        $request->getSession()->getFlashBag()->add('success', 'Utilisateur supprimés avec succès.');

        return $this->redirectToRoute('utilisateur_index');
    }

}
