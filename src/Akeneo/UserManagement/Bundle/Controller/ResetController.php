<?php

namespace Akeneo\UserManagement\Bundle\Controller;

use Akeneo\UserManagement\Bundle\Form\Handler\ResetHandler;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Bundle\Notification\MailResetNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ResetController extends AbstractController
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly SessionInterface $session,
        private readonly ResetHandler $resetHandler,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly FormInterface $form,
        private readonly MailResetNotifier $mailer,
    ) {
    }

    public function request(): Response
    {
        return $this->render('@PimUser/Reset/request.html.twig');
    }

    /**
     * Request reset user password
     */
    public function sendEmail(Request $request): Response
    {
        $username = $request->request->get('username');
        $user = $this->userManager->findUserByUsernameOrEmail($username);

        if (null === $user || false === $user->isEnabled()) {
            return $this->render('@PimUser/Reset/sendEmail.html.twig');
        }

        if ($user->isPasswordRequestNonExpired($this->container->getParameter('pim_user.reset.ttl'))) {
            $this->addFlash(
                'warn',
                'The password for this user has already been requested within the last 24 hours.'
            );

            return $this->redirectToRoute('pim_user_reset_request');
        }

        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($user->generateToken());
        }

        $user->setPasswordRequestedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        $this->userManager->updateUser($user);

        $this->mailer->notify($user);

        return $this->render('@PimUser/Reset/sendEmail.html.twig');
    }

    /**
     * Reset user password
     */
    public function reset(string $token): Response
    {
        $user = $this->userManager->findUserByConfirmationToken($token);

        if (null === $user || false === $user->isEnabled()) {
            throw $this->createNotFoundException(
                sprintf('The user with "confirmation token" does not exist for value "%s"', $token)
            );
        }

        if (!$user->isPasswordRequestNonExpired($this->container->getParameter('pim_user.reset.ttl'))) {
            $this->addFlash(
                'warn',
                'The password for this user has already been requested within the last 24 hours.'
            );

            return $this->redirectToRoute('pim_user_reset_request');
        }

        if ($this->resetHandler->process($user)) {
            $this->addFlash('success', 'Your password has been successfully reset. You may login now.');

            // force user logout
            $this->session->invalidate();
            $this->tokenStorage->setToken(null);

            return $this->redirectToRoute('pim_user_security_login');
        }

        return $this->render('@PimUser/Reset/reset.html.twig', [
            'token' => $token,
            'form' => $this->form->createView(),
        ]);
    }
}
