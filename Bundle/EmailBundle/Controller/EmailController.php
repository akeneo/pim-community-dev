<?php

namespace Oro\Bundle\EmailBundle\Controller;

use Oro\Bundle\EmailBundle\Decoder\ContentDecoder;
use Oro\Bundle\EmailBundle\Entity\Util\EmailUtil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\EmailBundle\Cache\EmailCacheManager;
use Oro\Bundle\EmailBundle\Entity\Repository\EmailRepository;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Entity\EmailBody;
use Oro\Bundle\EmailBundle\Entity\EmailAttachment;
use Oro\Bundle\EmailBundle\Form\Model\Email as EmailModel;
use Oro\Bundle\EmailBundle\Entity\EmailInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\EmailBundle\Entity\Repository\EmailAddressRepository;

class EmailController extends Controller
{
    protected $userNameFormat = null;

    /**
     * @Route("/view/{id}", name="oro_email_view", requirements={"id"="\d+"})
     * @Acl(
     *      id="oro_email_view",
     *      type="entity",
     *      class="OroEmailBundle:Email",
     *      permission="VIEW"
     * )
     * @Template
     */
    public function viewAction(Email $entity)
    {
        $this->getEmailCacheManager()->ensureEmailBodyCached($entity);

        return array(
            'entity' => $entity
        );
    }

    /**
     * @Route("/create")
     * @Acl(
     *      id="oro_email_create",
     *      type="entity",
     *      class="OroEmailBundle:Email",
     *      permission="CREATE"
     * )
     * @Template("OroEmailBundle:Email:update.html.twig")
     */
    public function createAction()
    {
        $entity = new EmailModel();
        if ($this->getRequest()->query->has('from')) {
            $entity->setFrom($this->getRequest()->query->get('from'));
        } else {
            $nameFormat = $this->get('oro_config.twig.config_extension')
                ->getUserValue('oro_locale.name_format');

            /** @var User $user */
            $user = $this->getUser();
            $entity->setFrom(
                EmailUtil::buildFullEmailAddress(
                    $user->getEmail(),
                    $this->getOwnerName($user->getFirstname(), $user->getLastname())
                )
            );
        }
        if ($this->getRequest()->query->has('to')) {
            $to = trim($this->getRequest()->query->get('to'));
            if (!empty($to)) {
                if (!EmailUtil::isFullEmailAddress($to)) {
                    /** @var EmailAddressRepository $repo */
                    $repo = $this->get('oro_email.email.address.manager')
                        ->getEmailAddressRepository($this->getDoctrine()->getManager());
                    $emailAddress = $repo->findOneByEmail($to);
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
                $entity->addTo($to);
            }
        }

        if ($this->get('oro_email.form.handler.email')->process($entity)) {

        }

        return array(
            'entity' => $entity,
            'form'   => $this->get('oro_email.form.email')->createView(),
        );
    }

    /**
     * Get email list
     * TODO: This is a temporary action created for demo purposes. It will be removed when 'display activities'
     *       functionality is implemented
     *
     * @AclAncestor("oro_email_view")
     * @Template
     */
    public function activitiesAction($emails)
    {
        /** @var $emailRepository EmailRepository */
        $emailRepository = $this->getDoctrine()->getRepository('OroEmailBundle:Email');

        $emails = EmailUtil::extractEmailAddresses($emails);
        if (empty($emails)) {
            $qb = $emailRepository->createEmailListForAddressesQueryBuilder();
            $qb->setParameter(EmailRepository::EMAIL_ADDRESSES, $emails);
            $rows = $qb->getQuery()->execute();
        } else {
            $rows = array();
        }

        return array(
            'entities' => $rows
        );
    }

    /**
     * Get the given email body content
     *
     * @Route("/body/{id}", name="oro_email_body", requirements={"id"="\d+"})
     * @AclAncestor("oro_email_view")
     */
    public function bodyAction(EmailBody $entity)
    {
        return new Response($entity->getContent());
    }

    /**
     * Get a response for download the given email attachment
     *
     * @Route("/attachment/{id}", name="oro_email_attachment", requirements={"id"="\d+"})
     * @AclAncestor("oro_email_view")
     */
    public function attachmentAction(EmailAttachment $entity)
    {
        $response = new Response();
        $response->headers->set('Content-Type', $entity->getContentType());
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $entity->getFileName()));
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        $content = ContentDecoder::decode(
            $entity->getContent()->getValue(),
            $entity->getContent()->getContentTransferEncoding()
        );
        $response->setContent($content);

        return $response;
    }

    /**
     * Get email cache manager
     *
     * @return EmailCacheManager
     */
    protected function getEmailCacheManager()
    {
        return $this->container->get('oro_email.email.cache.manager');
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

    /**
     * Gets a string used to format email address owner name
     *
     * @return string
     */
    protected function getUserNameFormat()
    {
        if ($this->userNameFormat === null) {
            $this->userNameFormat = $this->get('oro_config.twig.config_extension')
                ->getUserValue('oro_locale.name_format');
        }

        return $this->userNameFormat;
    }
}
