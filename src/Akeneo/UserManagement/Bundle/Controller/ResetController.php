<?php

namespace Akeneo\UserManagement\Bundle\Controller;

use Akeneo\UserManagement\Bundle\Form\Handler\ResetHandler;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Bundle\Notification\MailResetNotifier;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ResetController extends AbstractController
{
    const SESSION_EMAIL = 'pim_user_reset_email';

    public function __construct(
        private UserManager           $userManager,
        private SessionInterface      $session,
        private ResetHandler          $resetHandler,
        private TokenStorageInterface $tokenStorage,
        private FormInterface         $form,
        private MailResetNotifier     $mailer
    ) {
    }

    /**
     * @Template("@PimUser/Reset/request.html.twig")
     */
    public function request(): array
    {
        return [];
    }

    /**
     * Request reset user password
     *
     * @Template("@PimUser/Reset/sendEmail.html.twig")
     */
    public function sendEmail(Request $request): RedirectResponse|array
    {
        $username = $request->request->get('username');
        $user = $this->userManager->findUserByUsernameOrEmail($username);

        if (null === $user || false === $user->isEnabled()) {
            return [];
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

        $this->session->set(static::SESSION_EMAIL, $this->getObfuscatedEmail($user));

        $user->setPasswordRequestedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        $this->userManager->updateUser($user);

        $this->mailer->notify($user);

        return [];
    }

    /**
     * Tell the user to check his email provider
     *
     * @Template
     */
    public function checkEmail()
    {
        $email = $this->session->get(static::SESSION_EMAIL);

        $this->session->remove(static::SESSION_EMAIL);

        if (empty($email)) {
            // the user does not come from the sendEmail action
            return $this->redirectToRoute('pim_user_reset_request');
        }

        return [
            'email' => $email,
        ];
    }

    /**
     * Reset user password
     *
     * @Template("@PimUser/Reset/reset.html.twig")
     */
    public function reset($token)
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

        return [
            'token' => $token,
            'form' => $this->form->createView(),
        ];
    }

    /**
     * Get the truncated email displayed when requesting the resetting.
     * The default implementation only keeps the part following @ in the address.
     *
     * @param UserInterface $user
     *
     * @return string
     */
    protected function getObfuscatedEmail(UserInterface $user)
    {
        $email = $user->getEmail();

        if (false !== $pos = strpos($email, '@')) {
            $email = '...' . substr($email, $pos);
        }

        return $email;
    }
}
