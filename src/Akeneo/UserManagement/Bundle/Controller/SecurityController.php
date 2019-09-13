<?php

namespace Akeneo\UserManagement\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;

class SecurityController extends Controller
{
    /** @var AuthenticationUtils */
    private $authenticationUtils;

    /** @var CsrfTokenManagerInterface */
    private $csrfTokenManager;

    /** @var string */
    private $actionRoute;

    /** @var string */
    private $additionalHiddenFields;

    /** @var LogoutUrlGenerator */
    private $logoutUrlGenerator;

    public function __construct(
        AuthenticationUtils $authenticationUtils,
        CsrfTokenManagerInterface $csrfTokenManager,
        LogoutUrlGenerator $logoutUrlGenerator,
        string $actionRoute,
        array $additionalHiddenFields
    ) {
        $this->authenticationUtils = $authenticationUtils;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->logoutUrlGenerator = $logoutUrlGenerator;
        $this->actionRoute = $actionRoute;
        $this->additionalHiddenFields = $additionalHiddenFields;
    }

    /**
     * @Template("PimUserBundle:Security:login.html.twig")
     */
    public function login()
    {
        // get the login error if there is one
        $error = $this->authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $this->authenticationUtils->getLastUsername();
        $csrfToken = $this->csrfTokenManager->getToken('authenticate')->getValue();

        return [
            // last username entered by the user
            'last_username'            => $lastUsername,
            'csrf_token'               => $csrfToken,
            'error'                    => $error,
            'action_route'             => $this->actionRoute,
            'additional_hidden_fields' => $this->additionalHiddenFields,
        ];
    }

    public function check()
    {
        throw new \RuntimeException(
            'You must configure the check path to be handled by the firewall ' .
            'using form_login in your security firewall configuration.'
        );
    }

    public function logout()
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }

    public function logoutRedirect()
    {
        return $this->redirect($this->logoutUrlGenerator->getLogoutUrl());
    }
}
