<?php

namespace Oro\Bundle\EmailBundle\Form\Handler;

use Oro\Bundle\EmailBundle\Entity\EmailFolder;
use Oro\Bundle\EmailBundle\Entity\InternalEmailOrigin;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\EmailBundle\Form\Model\Email;
use Oro\Bundle\EmailBundle\Builder\EmailEntityBuilder;
use Oro\Bundle\EmailBundle\Mailer\DirectMailSender;
use Oro\Bundle\EmailBundle\Entity\Util\EmailUtil;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Oro\Bundle\ConfigBundle\Twig\ConfigExtension;

class EmailHandler
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var EmailAddressManager
     */
    protected $emailAddressManager;

    /**
     * @var EmailEntityBuilder
     */
    protected $emailEntityBuilder;

    /**
     * @var DirectMailSender
     */
    protected $mailer;

    /**
     * @var ConfigExtension
     */
    protected $configExtension;

    public function __construct(
        FormInterface $form,
        Request $request,
        EntityManager $em,
        SecurityContextInterface $securityContext,
        EmailAddressManager $emailAddressManager,
        EmailEntityBuilder $emailEntityBuilder,
        DirectMailSender $mailer,
        ConfigExtension $configExtension
    ) {
        $this->form                = $form;
        $this->request             = $request;
        $this->em                  = $em;
        $this->securityContext     = $securityContext;
        $this->emailAddressManager = $emailAddressManager;
        $this->emailEntityBuilder  = $emailEntityBuilder;
        $this->mailer              = $mailer;
        $this->configExtension     = $configExtension;
    }

    /**
     * Process form
     *
     * @param  Email $model
     * @return bool True on successful processing, false otherwise
     */
    public function process(Email $model)
    {
        if ($this->request->getMethod() === 'GET') {
            $this->initModel($model);
        }
        $this->form->setData($model);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $messageDate = new \DateTime('now', new \DateTimeZone('UTC'));
                $message     = $this->mailer->createMessage();
                $message->setDate($messageDate->getTimestamp());
                $message->setSubject($model->getSubject());
                $message->setFrom($this->mailer->getAddresses($model->getFrom()));
                $message->setTo($this->mailer->getAddresses($model->getTo()));
                $message->setBody($model->getBody(), 'text/plain');
                $sent = $this->mailer->send($message);

                if ($sent) {
                    $origin = $this->em->getRepository('OroEmailBundle:InternalEmailOrigin')
                        ->findOneBy(array('name' => InternalEmailOrigin::BAP));
                    $this->emailEntityBuilder->setOrigin($origin);
                    $email = $this->emailEntityBuilder->email(
                        $model->getSubject(),
                        $model->getFrom(),
                        $model->getTo(),
                        $messageDate,
                        $messageDate,
                        $messageDate
                    );
                    $email->setFolder($origin->getFolder(EmailFolder::SENT));
                    $emailBody = $this->emailEntityBuilder->body($model->getBody(), false, true);
                    $email->setEmailBody($emailBody);
                    $this->emailEntityBuilder->getBatch()->persist($this->em);
                    $this->em->flush();
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Populate a model with initial data.
     * This method is used to load an initial data from a query string
     *
     * @param Email $model
     */
    protected function initModel(Email $model)
    {
        if ($this->request->query->has('from')) {
            $model->setFrom($this->request->query->get('from'));
        } else {
            $user = $this->getUser();
            $model->setFrom(
                EmailUtil::buildFullEmailAddress(
                    $user->getEmail(),
                    $this->getOwnerName($user->getFirstname(), $user->getLastname())
                )
            );
        }
        if ($this->request->query->has('to')) {
            $to = trim($this->request->query->get('to'));
            if (!empty($to)) {
                if (!EmailUtil::isFullEmailAddress($to)) {
                    $repo         = $this->emailAddressManager->getEmailAddressRepository($this->em);
                    $emailAddress = $repo->findOneBy(array('email' => $to));
                    if ($emailAddress) {
                        $owner = $emailAddress->getOwner();
                        if ($owner) {
                            $to = EmailUtil::buildFullEmailAddress(
                                $to,
                                $this->getOwnerName($owner->getFirstname(), $owner->getLastname())
                            );
                        }
                    }
                }
                $model->setTo(array($to));
            }
        }
        if ($this->request->query->has('subject')) {
            $subject = trim($this->request->query->get('subject'));
            $model->setSubject($subject);
        }
    }

    /**
     * Get the current authenticated user
     *
     * @return UserInterface|null
     */
    protected function getUser()
    {
        $token = $this->securityContext->getToken();
        if ($token) {
            $user = $token->getUser();
            if ($user instanceof UserInterface) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Returns email address owner name formatted based on system configuration
     *
     * @param string $firstName
     * @param string $lastName
     * @return string
     */
    protected function getOwnerName($firstName, $lastName)
    {
        return str_replace(
            array('%first%', '%last%'),
            array($firstName, $lastName),
            $this->getUserNameFormat()
        );
    }

    protected $userNameFormat = null;

    /**
     * Gets a string used to format email address owner name
     *
     * @return string
     */
    protected function getUserNameFormat()
    {
        if ($this->userNameFormat === null) {
            $this->userNameFormat = $this->configExtension
                ->getUserValue('oro_locale.name_format');
        }

        return $this->userNameFormat;
    }
}
