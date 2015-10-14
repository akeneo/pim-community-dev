<?php
namespace Pim\Bundle\UserBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ResetController
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
     */
    public function sendEmailAction(Request $request)
    {
        $username = $request->request->get('username');
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);

        if (null === $user) {
            return $this->render('PimUserBundle:Reset:request.html.twig', ['invalid_username' => $username]);
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

        /**
         * Get the truncated email displayed when requesting the resetting.
         * The default implementation only keeps the part following @ in the address.
         */
        $ofuscatedEmail = $user->getEmail();

        if (false !== $pos = strpos($ofuscatedEmail, '@')) {
            $ofuscatedEmail = '...' . substr($ofuscatedEmail, $pos);
        }

        $this->get('session')->set(static::SESSION_EMAIL, $ofuscatedEmail);

        /**
         * @todo Move to postUpdate lifecycle event handler as service
         */
        $message = \Swift_Message::newInstance()
            ->setSubject('Reset password')
            ->setFrom($this->container->getParameter('pim_user.email'))
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView('PimUserBundle:Mail:reset.html.twig', ['user' => $user]),
                'text/html'
            );

        $user->setPasswordRequestedAt(new \DateTime('now', new \DateTimeZone('UTC')));

        $this->get('mailer')->send($message);
        $this->get('pim_user.saver.user')->save($user);

        return $this->redirect($this->generateUrl('pim_user_reset_check_email'));
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
    public function resetAction(Request $request, $token)
    {
        $user    = $this->get('pim_user.repository.user')->findOneBy(['confirmationToken' => $token]);
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

        $form = $this->createForm('pim_user_reset', $user);

        if ($request->isMethod('POST')) {
            $form->submit($request);

            if ($form->isValid()) {
                $user
                    ->setPlainPassword($form->getData()->getPlainPassword())
                    ->setConfirmationToken(null)
                    ->setPasswordRequestedAt(null)
                    ->setEnabled(true);

                $this->get('pim_user.saver.user')->save($user);

                $session->getFlashBag()->add('success', 'Your password has been successfully reset. You may login now.');
                $session->invalidate();
                $this->get('security.token_storage')->setToken(null);

                return $this->redirect($this->generateUrl('pim_user_security_login'));
            }
        }

        return [
            'token' => $token,
            'form'  => $form->createView(),
        ];
    }
}
