<?php
namespace Oro\Bundle\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\SecurityBundle\Annotation\Acl;

class ResetController extends Controller
{
    const SESSION_EMAIL = 'oro_user_reset_email';

    /**
     * @Route("/reset-request", name="oro_user_reset_request")
     * @Method({"GET"})
     * @Template
     * @Acl(
     *      id = "oro_reset_request",
     *      label="reset password",
     *      type="action",
     *      group_name=""
     * )
     */
    public function requestAction()
    {
        return array();
    }

    /**
     * Request reset user password
     *
     * @Route("/send-email", name="oro_user_reset_send_email")
     * @Method({"POST"})
     * @Acl(
     *      id = "oro_reset_send_mail",
     *      label="Request reset user password",
     *      type="action",
     *      group_name=""
     * )
     */
    public function sendEmailAction()
    {
        $username = $this->getRequest()->request->get('username');
        $user = $this->get('oro_user.manager')->findUserByUsernameOrEmail($username);

        if (null === $user) {
            return $this->render('OroUserBundle:Reset:request.html.twig', array('invalid_username' => $username));
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
        $message = \Swift_Message::newInstance()
            ->setSubject('Reset password')
            ->setFrom($this->container->getParameter('oro_user.email'))
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView('OroUserBundle:Mail:reset.html.twig', array('user' => $user)),
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
     * @Route("/check-email", name="oro_user_reset_check_email")
     * @Method({"GET"})
     * @Template
     * @Acl(
     *      id = "oro_reset_check_email",
     *      label="Tell the user to check his email provider",
     *      type="action",
     *      group_name=""
     * )
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

        return array(
            'email' => $email,
        );
    }

    /**
     * Reset user password
     *
     * @Route("/reset/{token}", name="oro_user_reset_reset", requirements={"token"="\w+"})
     * @Method({"GET", "POST"})
     * @Template
     * @Acl(
     *      id = "oro_reset_password",
     *      label="Reset user password",
     *      type="action",
     *      group_name=""
     * )
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
            $this->get('security.context')->setToken(null);

            return $this->redirect($this->generateUrl('oro_user_security_login'));
        }

        return array(
            'token' => $token,
            'form'  => $this->get('oro_user.form.reset')->createView(),
        );
    }

    /**
     * Get the truncated email displayed when requesting the resetting.
     * The default implementation only keeps the part following @ in the address.
     *
     * @param User $user
     *
     * @return string
     */
    protected function getObfuscatedEmail(User $user)
    {
        $email = $user->getEmail();

        if (false !== $pos = strpos($email, '@')) {
            $email = '...' . substr($email, $pos);
        }

        return $email;
    }
}
