<?php

namespace Oro\Bundle\EmailBundle\Cache;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Entity\EmailBody;
use Oro\Bundle\EmailBundle\Entity\EmailAttachment;
use Oro\Bundle\EmailBundle\Entity\EmailAttachmentContent;

class EmailCacheManager
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Check that email body is cached. If not load it through an email service connector and add it to a cache
     *
     * @param Email $email
     */
    public function ensureEmailBodyCached(Email $email)
    {
        if ($email->getEmailBody() !== null) {
            // The email body is already cached
            return;
        }

        // TODO: implement getting email details through correct connector here

        //$emailOriginName = $email->getFolder()->getOrigin()->getName();
        //$connector = $this->get(sprintf('oro_%s.connector', $emailOriginName));

        $emailBody = new EmailBody();
        $emailBody
            ->setHeader($email)
            ->setContent("<html><body>\n<h1>Sample Email Body</h1>\n some text \n some text \n some text \n some text \n some text</body></html>");

        $emailBody->addAttachment(
            $this->createEmailAttachment(
                'sample attachment file.txt',
                'text/plain',
                'binary',
                'some text'
            )
        );

        $emailBody->addAttachment(
            $this->createEmailAttachment(
                'sample attachment file (base64).txt',
                'text/plain',
                'base64',
                'some text'
            )
        );

        $email->setEmailBody($emailBody);

        $this->em->persist($email);
        $this->em->flush();
    }

    /**
     * Create CreateEmailAttachment object
     *
     * @param string $fileName
     * @param string $contentType
     * @param string $contentTransferEncoding
     * @param string $content
     * @return EmailAttachment
     */
    protected function createEmailAttachment($fileName, $contentType, $contentTransferEncoding, $content)
    {
        $emailAttachmentContent = new EmailAttachmentContent();
        $emailAttachmentContent
            ->setContentTransferEncoding($contentTransferEncoding)
            ->setValue($content);

        $emailAttachment = new EmailAttachment();
        $emailAttachment
            ->setFileName($fileName)
            ->setContentType($contentType)
            ->setContent($emailAttachmentContent);

        return $emailAttachment;
    }
}
