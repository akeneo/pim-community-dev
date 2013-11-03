<?php

namespace Oro\Bundle\EmailBundle\Controller;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
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
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class EmailController extends Controller
{
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
        $responseData = array(
            'entity' => $entity,
            'saved' => false
        );
        if ($this->get('oro_email.form.handler.email')->process($entity)) {
            $responseData['saved'] = true;
        }
        $responseData['form'] = $this->get('oro_email.form.email')->createView();

        return $responseData;
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
        return array(
            'entities' => array()
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
}
