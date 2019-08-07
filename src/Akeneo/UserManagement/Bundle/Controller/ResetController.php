<?php

namespace Akeneo\UserManagement\Bundle\Controller;

use Akeneo\UserManagement\Bundle\Form\Handler\ResetHandler;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ResetController extends Controller
{
    const SESSION_EMAIL = 'pim_user_reset_email';

    /** @var UserManager */
    private $userManager;

    /** @var Swift_Mailer */
    private $mailer;

    /** @var SessionInterface */
    private $session;

    /** @var ResetHandler */
    private $resetHandler;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var FormInterface */
    private $form;

    public function __construct(
        UserManager $userManager,
        Swift_Mailer $mailer,
        SessionInterface $session,
        ResetHandler $resetHandler,
        TokenStorageInterface $tokenStorage,
        FormInterface $form
    ) {
        $this->userManager = $userManager;
        $this->mailer = $mailer;
        $this->session = $session;
        $this->resetHandler = $resetHandler;
        $this->tokenStorage = $tokenStorage;
        $this->form = $form;
    }

    /**
     * @Template("PimUserBundle:Reset:request.html.twig")
     */
    public function request()
    {
        return [];
    }

    /**
     * Request reset user password
     *
     * @Template("PimUserBundle:Reset:sendEmail.html.twig")
     */
    public function sendEmail(Request $request)
    {
        $username = $request->request->get('username');
        $user = $this->userManager->findUserByUsernameOrEmail($username);

        if (null === $user) {
            return [];
        }

        if ($user->isPasswordRequestNonExpired($this->container->getParameter('pim_user.reset.ttl'))) {
            $this->addFlash(
                'warn',
                'The password for this user has already been requested within the last 24 hours.'
            );

            return $this->redirect($this->generateUrl('pim_user_reset_request'));
        }

        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($user->generateToken());
        }

        $this->session->set(static::SESSION_EMAIL, $this->getObfuscatedEmail($user));

        /**
         * @todo Move to postUpdate lifecycle event handler as service
         */
        $message = (new \Swift_Message('Reset password'))
            ->setFrom($this->container->getParameter('pim_user.email'))
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView('PimUserBundle:Mail:reset.html.twig', ['user' => $user]),
                'text/html'
            );

        $user->setPasswordRequestedAt(new \DateTime('now', new \DateTimeZone('UTC')));

        $this->mailer->send($message);
        $this->userManager->updateUser($user);

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
            return $this->redirect($this->generateUrl('pim_user_reset_request'));
        }

        return [
            'email' => $email,
        ];
    }

    /**
     * Reset user password
     *
     * @Template("PimUserBundle:Reset:reset.html.twig")
     */
    public function reset($token)
    {
        $user = $this->userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw $this->createNotFoundException(
                sprintf('The user with "confirmation token" does not exist for value "%s"', $token)
            );
        }

        if (!$user->isPasswordRequestNonExpired($this->container->getParameter('pim_user.reset.ttl'))) {
            $this->addFlash(
                'warn',
                'The password for this user has already been requested within the last 24 hours.'
            );

            return $this->redirect($this->generateUrl('pim_user_reset_request'));
        }

        if ($this->resetHandler->process($user)) {
            $this->addFlash('success', 'Your password has been successfully reset. You may login now.');

            // force user logout
            $this->session->invalidate();
            $this->tokenStorage->setToken(null);

            return $this->redirect($this->generateUrl('pim_user_security_login'));
        }

        return [
            'token' => $token,
            'form'  => $this->form->createView(),
        ];
    }

    /**
     * Get the truncated email displayed when requesting the resetting.
     * The default implementation only keeps the part following @ in the address.
     *
     * @param \Akeneo\UserManagement\Component\Model\UserInterface $user
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
