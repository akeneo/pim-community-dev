<?php

namespace Oro\Bundle\EmailBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EmailBundle\Datagrid\EmailTemplateDatagridManager;

/**
 * @Route("/emailtemplate")
 * @Acl(
 *      id="oro_email_emailtemplate",
 *      name="Email templates",
 *      description="Email templates manipulation",
 *      parent="root"
 * )
 */
class EmailTemplateController extends Controller
{
    /**
     * @Route(
     *      "/{_format}",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Acl(
     *      id="oro_email_emailtemplate_index",
     *      name="View List of email templates",
     *      description="View List of email templates",
     *      parent="oro_email_emailtemplate"
     * )
     * @Template()
     */
    public function indexAction()
    {
        /** @var EmailTemplateDatagridManager $gridManager */
        $gridManager = $this->get('oro_email.emailtemplate.datagrid_manager');
        $datagridView = $gridManager->getDatagrid()->createView();

        if ('json' == $this->getRequest()->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
        }

        return array('datagrid' => $datagridView);
    }

    /**
     * @Route("/update/{id}", requirements={"id"="\d+"}, defaults={"id"=0}))
     * @Acl(
     *      id="oro_email_emailtemplate_update",
     *      name="Edit email template",
     *      description="Edit email template",
     *      parent="oro_email_emailtemplate"
     * )
     * @Template()
     */
    public function updateAction(EmailTemplate $entity, $isClone = false)
    {
        if ($this->get('oro_email.form.handler.emailtemplate')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('oro.email.controller.emailtemplate.saved.message')
            );

            return $this->get('oro_ui.router')->actionRedirect(
                array(
                    'route' => 'oro_email_emailtemplate_update',
                    'parameters' => array('id' => $entity->getId()),
                ),
                array(
                    'route' => 'oro_email_emailtemplate_index',
                )
            );
        }

        return array(
            'form'    => $this->get('oro_email.form.emailtemplate')->createView(),
            'isClone' => $isClone
        );
    }

    /**
     * @Route("/create")
     * @Acl(
     *      id="oro_email_emailtemplate_create",
     *      name="Create email template",
     *      description="Create email template",
     *      parent="oro_email_emailtemplate"
     * )
     * @Template("OroEmailBundle:EmailTemplate:update.html.twig")
     */
    public function createAction()
    {
        return $this->updateAction(new EmailTemplate());
    }

    /**
     * @Route("/clone/{id}", requirements={"id"="\d+"}, defaults={"id"=0}))
     * @Acl(
     *      id="oro_email_emailtemplate_clone",
     *      name="Clone email template",
     *      description="Clone email template",
     *      parent="oro_email_emailtemplate"
     * )
     * @Template("OroEmailBundle:EmailTemplate:update.html.twig")
     */
    public function cloneAction(EmailTemplate $entity)
    {
        return $this->updateAction(clone $entity, true);
    }

    /**
     * @Route("/preview/{id}", requirements={"id"="\d+"}, defaults={"id"=0}))
     * @Acl(
     *      id="oro_email_emailtemplate_preview",
     *      name="Preview email template",
     *      description="Preview email template",
     *      parent="oro_email_emailtemplate"
     * )
     * @Template("OroEmailBundle:EmailTemplate:preview.html.twig")
     * @param EmailTemplate $emailTemplate
     * @return array
     */
    public function previewAction(EmailTemplate $emailTemplate)
    {
        $sampleEntity = $emailTemplate->getEntityName();
        $sampleEntity = new $sampleEntity();

        list ($subjectRendered, $templateRendered) = $this->get('oro_email.email_renderer')
            ->compileMessage($emailTemplate, array('entity' => $sampleEntity));

        return array(
            'subject' => $subjectRendered,
            'content' => $templateRendered,
        );
    }
}
