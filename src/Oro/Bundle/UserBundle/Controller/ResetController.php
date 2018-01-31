<?php
namespace Oro\Bundle\UserBundle\Controller;

use Pim\Bundle\UserBundle\Entity\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ResetController extends Controller
{
    const SESSION_EMAIL = 'oro_user_reset_email';

    /**
     * @Template
     */
    public function requestAction()
    {
        return [];
    }

    /**
     * Request reset user password
     */
    public function sendEmailAction(Request $request)
    {
        $username = $request->request->get('username');
        $user = $this->get('oro_user.manager')->findUserByUsernameOrEmail($username);

        if (null === $user) {
            return $this->render('OroUserBundle:Reset:request.html.twig', ['invalid_username' => $username]);
        }

        if ($user->isPasswordRequestNonExpired($this->container->getParameter('oro_user.reset.ttl'))) {
            $this->get('session')->getFlashBag()->add(
                'warn',
                'The password for this user has already been requested within the last 24 hours.'
            );

            return $this->redirect($this->generateUrl('oro_user_reset_request'));
        }

        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($user->generateToken());
        }

        $this->get('session')->set(static::SESSION_EMAIL, $this->getObfuscatedEmail($user));

        /**
         * @todo Move to postUpdate lifecycle event handler as service
         */
        $message = (new \Swift_Message('Reset password'))
            ->setFrom($this->container->getParameter('oro_user.email'))
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView('OroUserBundle:Mail:reset.html.twig', ['user' => $user]),
                'text/html'
            );

        $user->setPasswordRequestedAt(new \DateTime('now', new \DateTimeZone('UTC')));

        $this->get('mailer')->send($message);
        $this->get('oro_user.manager')->updateUser($user);

        return $this->redirect($this->generateUrl('oro_user_reset_check_email'));
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
            return $this->redirect($this->generateUrl('oro_user_reset_request'));
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
        $user = $this->get('oro_user.manager')->findUserByConfirmationToken($token);
        $session = $this->get('session');

        if (null === $user) {
            throw $this->createNotFoundException(
                sprintf('The user with "confirmation token" does not exist for value "%s"', $token)
            );
        }

        if (!$user->isPasswordRequestNonExpired($this->container->getParameter('oro_user.reset.ttl'))) {
            $session->getFlashBag()->add(
                'warn',
                'The password for this user has already been requested within the last 24 hours.'
            );

            return $this->redirect($this->generateUrl('oro_user_reset_request'));
        }

        if ($this->get('oro_user.form.handler.reset')->process($user)) {
            $session->getFlashBag()->add('success', 'Your password has been successfully reset. You may login now.');

            // force user logout
            $session->invalidate();
            $this->get('security.token_storage')->setToken(null);

            return $this->redirect($this->generateUrl('oro_user_security_login'));
        }

        return [
            'token' => $token,
            'form'  => $this->get('oro_user.form.reset')->createView(),
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
