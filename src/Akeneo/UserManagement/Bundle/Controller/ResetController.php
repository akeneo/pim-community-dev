<?php
namespace Akeneo\UserManagement\Bundle\Controller;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ResetController extends Controller
{
    const SESSION_EMAIL = 'pim_user_reset_email';

    /**
     * @Template
     */
    public function requestAction()
    {
        return [];
    }

    /**
     * Request reset user password
     *
     * @Template
     */
    public function sendEmailAction(Request $request)
    {
        $username = $request->request->get('username');
        $user = $this->get('pim_user.manager')->findUserByUsernameOrEmail($username);

        if (null === $user) {
            return [];
        }

        if ($user->isPasswordRequestNonExpired($this->container->getParameter('pim_user.reset.ttl'))) {
            $this->get('session')->getFlashBag()->add(
                'warn',
                'The password for this user has already been requested within the last 24 hours.'
            );

            return $this->redirect($this->generateUrl('pim_user_reset_request'));
        }

        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($user->generateToken());
        }

        $this->get('session')->set(static::SESSION_EMAIL, $this->getObfuscatedEmail($user));

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

        $this->get('mailer')->send($message);
        $this->get('pim_user.manager')->updateUser($user);

        return [];
    }

    /**
     * Tell the user to check his email provider
     *
     * @Template
     */
    public function checkEmailAction()
    {
        $session = $this->get('session');
        $email = $session->get(static::SESSION_EMAIL);

        $session->remove(static::SESSION_EMAIL);

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
     * @Template
     */
    public function resetAction($token)
    {
        $user = $this->get('pim_user.manager')->findUserByConfirmationToken($token);
        $session = $this->get('session');

        if (null === $user) {
            throw $this->createNotFoundException(
                sprintf('The user with "confirmation token" does not exist for value "%s"', $token)
            );
        }

        if (!$user->isPasswordRequestNonExpired($this->container->getParameter('pim_user.reset.ttl'))) {
            $session->getFlashBag()->add(
                'warn',
                'The password for this user has already been requested within the last 24 hours.'
            );

            return $this->redirect($this->generateUrl('pim_user_reset_request'));
        }

        if ($this->get('pim_user.form.handler.reset')->process($user)) {
            $session->getFlashBag()->add('success', 'Your password has been successfully reset. You may login now.');

            // force user logout
            $session->invalidate();
            $this->get('security.token_storage')->setToken(null);

            return $this->redirect($this->generateUrl('pim_user_security_login'));
        }

        return [
            'token' => $token,
            'form'  => $this->get('pim_user.form.reset')->createView(),
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
