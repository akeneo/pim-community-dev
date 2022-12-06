<?php

namespace Akeneo\UserManagement\Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;

class SecurityController extends AbstractController
{
    private AuthenticationUtils $authenticationUtils;
    private CsrfTokenManagerInterface $csrfTokenManager;
    private string $actionRoute;
    private array $additionalHiddenFields;
    private LogoutUrlGenerator $logoutUrlGenerator;

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

    public function login(): Response
    {
        // get the login error if there is one
        $error = $this->authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $this->authenticationUtils->getLastUsername();
        $csrfToken = $this->csrfTokenManager->getToken('authenticate')->getValue();

        return $this->render('@PimUser/Security/login.html.twig', [
            // last username entered by the user
            'last_username'            => $lastUsername,
            'csrf_token'               => $csrfToken,
            'error'                    => $error,
            'action_route'             => $this->actionRoute,
            'additional_hidden_fields' => $this->additionalHiddenFields,
        ]);
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

    public function logoutRedirect(): RedirectResponse
    {
        return new RedirectResponse($this->logoutUrlGenerator->getLogoutUrl());
    }
}
