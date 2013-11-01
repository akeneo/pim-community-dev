<?php

namespace Oro\Bundle\EmailBundle\Form\Handler;

use Psr\Log\LoggerInterface;
use Oro\Bundle\EmailBundle\Entity\EmailFolder;
use Oro\Bundle\EmailBundle\Entity\InternalEmailOrigin;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\EmailBundle\Form\Model\Email;
use Oro\Bundle\EmailBundle\Builder\EmailEntityBuilder;
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
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

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
        \Swift_Mailer $mailer,
        LoggerInterface $logger,
        ConfigExtension $configExtension
    ) {
        $this->form                = $form;
        $this->request             = $request;
        $this->em                  = $em;
        $this->securityContext     = $securityContext;
        $this->emailAddressManager = $emailAddressManager;
        $this->emailEntityBuilder  = $emailEntityBuilder;
        $this->mailer              = $mailer;
        $this->logger              = $logger;
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
        $result = false;
        if ($this->request->getMethod() === 'GET') {
            $this->initModel($model);
        }
        $this->form->setData($model);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                try {
                    $messageDate = new \DateTime('now', new \DateTimeZone('UTC'));
                    $message     = $this->mailer->createMessage();
                    $message->setDate($messageDate->getTimestamp());
                    $message->setFrom($this->getAddresses($model->getFrom()));
                    $message->setTo($this->getAddresses($model->getTo()));
                    $message->setSubject($model->getSubject());
                    $message->setBody($model->getBody(), 'text/plain');
                    $sent = $this->mailer->send($message);
                    if (!$sent) {
                        throw new \Swift_SwiftException('An email was not delivered.');
                    }

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

                    $result = true;
                } catch (\Exception $ex) {
                    $this->logger->error('Email sending failed.', array('exception' => $ex));
                    $this->form->addError(new FormError('Unable to send the email.'));
                }
            }
        }

        return $result;
    }

    /**
     * Populate a model with initial data.
     * This method is used to load an initial data from a query string
     *
     * @param Email $model
     */
    protected function initModel(Email $model)
    {
        if ($this->request->query->has('gridName')) {
            $model->setGridName($this->request->query->get('gridName'));
        }
        if ($this->request->query->has('from')) {
            $from = $this->request->query->get('from');
            if (!empty($from)) {
                $this->preciseFullEmailAddress($from);
            }
            $model->setFrom($from);
        } else {
            $user = $this->getUser();
            if ($user) {
                $model->setFrom(
                    EmailUtil::buildFullEmailAddress(
                        $user->getEmail(),
                        $this->getOwnerName($user->getFirstname(), $user->getLastname())
                    )
                );
            }
        }
        if ($this->request->query->has('to')) {
            $to = trim($this->request->query->get('to'));
            if (!empty($to)) {
                $this->preciseFullEmailAddress($to);
            }
            $model->setTo(array($to));
        }
        if ($this->request->query->has('subject')) {
            $subject = trim($this->request->query->get('subject'));
            $model->setSubject($subject);
        }
    }

    /**
     * Converts emails addresses to a form acceptable to \Swift_Mime_Message class
     *
     * @param string|string[] $addresses Examples of correct email addresses: john@example.com, <john@example.com>,
     *                                   John Smith <john@example.com> or "John Smith" <john@example.com>
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function getAddresses($addresses)
    {
        $result = array();

        if (is_string($addresses)) {
            $addresses = array($addresses);
        }
        if (!is_array($addresses) && !$addresses instanceof \Iterator) {
            throw new \InvalidArgumentException(
                'The $addresses argument must be a string or a list of strings (array or Iterator)'
            );
        }

        foreach ($addresses as $address) {
            $name = EmailUtil::extractEmailAddressName($address);
            if (empty($name)) {
                $result[] = EmailUtil::extractPureEmailAddress($address);
            } else {
                $result[EmailUtil::extractPureEmailAddress($address)] = $name;
            }
        }

        return $result;
    }

    /**
     * @param string $emailAddress
     * @return string
     */
    protected function preciseFullEmailAddress(&$emailAddress)
    {
        if (!EmailUtil::isFullEmailAddress($emailAddress)) {
            $repo            = $this->emailAddressManager->getEmailAddressRepository($this->em);
            $emailAddressObj = $repo->findOneBy(array('email' => $emailAddress));
            if ($emailAddressObj) {
                $owner = $emailAddressObj->getOwner();
                if ($owner) {
                    $emailAddress = EmailUtil::buildFullEmailAddress(
                        $emailAddress,
                        $this->getOwnerName($owner->getFirstname(), $owner->getLastname())
                    );
                }
            }
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
