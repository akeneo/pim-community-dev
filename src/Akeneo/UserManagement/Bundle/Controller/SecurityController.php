<?php

namespace Akeneo\UserManagement\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SecurityController extends Controller
{
    /**
     * @Template("PimUserBundle:Security:login.html.twig")
     */
    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        $csrfToken = $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue();

        return [
            // last username entered by the user
            'last_username'            => $lastUsername,
            'csrf_token'               => $csrfToken,
            'error'                    => $error,
            'action_route'             => $this->getParameter('pim_user.login_form.action_route'),
            'additional_hidden_fields' => $this->getParameter('pim_user.login_form.additional_hidden_fields'),
        ];
    }

    public function checkAction()
    {
        throw new \RuntimeException(
            'You must configure the check path to be handled by the firewall ' .
            'using form_login in your security firewall configuration.'
        );
    }

    public function logoutAction()
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }

    public function logoutRedirectAction()
    {
        $logoutUrlGenerator = $this->get('security.logout_url_generator');

        return $this->redirect($logoutUrlGenerator->getLogoutUrl());
    }
}
        
