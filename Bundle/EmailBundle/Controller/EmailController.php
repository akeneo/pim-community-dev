<?php

namespace Oro\Bundle\EmailBundle\Controller;

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
use Oro\Bundle\EmailBundle\Entity\EmailInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;

/**
 * @Acl(
 *      id="oro_email",
 *      name="Email manipulation",
 *      description="Email manipulation"
 * )
 */
class EmailController extends Controller
{
    /**
     * @Route("/view/{id}", name="oro_email_view", requirements={"id"="\d+"})
     * @Acl(
     *      id="oro_email_view",
     *      name="View email",
     *      description="View email",
     *      parent="oro_email"
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

        $emails = $this->extractEmailAddresses($emails);
        $rows = empty($emails)
            ? array()
            : $emailRepository->getEmailListQueryBuilder($emails)->getQuery()->execute();

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
        $response->headers->set('Content-Transfer-Encoding', $entity->getContent()->getContentTransferEncoding());
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        $response->setContent($entity->getContent()->getValue());

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
}
