<?php

namespace Oro\Bundle\EmailBundle\Entity\Manager;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\EmailBundle\Entity\EmailAttachment;

class EmailApiEntityManager extends ApiEntityManager
{
    /**
     * Get email attachment entity by identifier.
     *
     * @param integer $id
     * @return EmailAttachment
     */
    public function findEmailAttachment($id)
    {
        return $this->getEmailAttachmentRepository()->find($id);
    }

    /**
     * Get email attachment repository
     *
     * @return ObjectRepository
     */
    public function getEmailAttachmentRepository()
    {
        return $this->getObjectManager()->getRepository('Oro\Bundle\EmailBundle\Entity\EmailAttachment');
    }
}
