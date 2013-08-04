<?php

namespace Oro\Bundle\EmailBundle\Controller\Api\Soap;

use Oro\Bundle\SoapBundle\Controller\Api\Soap\SoapGetController;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailApiEntityManager;
use Oro\Bundle\EmailBundle\Cache\EmailCacheManager;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Entity\EmailAttachmentContent;

class EmailController extends SoapGetController
{
    /**
     * @Soap\Method("getEmails")
     * @Soap\Param("page", phpType="int")
     * @Soap\Param("limit", phpType="int")
     * @Soap\Result(phpType = "Oro\Bundle\EmailBundle\Entity\Email")
     * @AclAncestor("oro_email_view")
     */
    public function cgetAction($page = 1, $limit = 10)
    {
        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * @Soap\Method("getEmail")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "Oro\Bundle\EmailBundle\Entity\Email")
     * @AclAncestor("oro_email_view")
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * @Soap\Method("getEmailBody")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="Oro\Bundle\EmailBundle\Entity\EmailBody")
     * @AclAncestor("oro_email_view")
     */
    public function getEmailBodyAction($id)
    {
        $entity = $this->getEntity($id);
        $this->getEmailCacheManager()->ensureEmailBodyCached($entity);

        return $entity->getEmailBody();
    }

    /**
     * @Soap\Method("getEmailAttachment")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="Oro\Bundle\EmailBundle\Entity\EmailAttachmentContent")
     * @AclAncestor("oro_email_view")
     */
    public function getEmailAttachment($id)
    {
        return $this->getEmailAttachmentContentEntity($id);
    }

    /**
     * Get email attachment by identifier.
     *
     * @param integer $attachmentId
     * @return EmailAttachmentContent
     * @throws \SoapFault
     */
    protected function getEmailAttachmentContentEntity($attachmentId)
    {
        $attachment = $this->getManager()->findEmailAttachment($attachmentId);

        if (!$attachment) {
            throw new \SoapFault('NOT_FOUND', sprintf('Record #%u can not be found', $attachmentId));
        }

        return $attachment->getContent();
    }

    /**
     * Get entity manager
     *
     * @return EmailApiEntityManager
     */
    public function getManager()
    {
        return $this->container->get('oro_email.manager.email.api');
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
}
