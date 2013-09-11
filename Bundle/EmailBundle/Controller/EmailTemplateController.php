<?php

namespace Oro\Bundle\EmailBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EmailBundle\Datagrid\EmailTemplateDatagridManager;

/**
 * @Route("/emailtemplate")
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
     *      type="entity",
     *      entity="OroEmailBundle:EmailTemplate",
     *      precision="VIEW"
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
     *      type="entity",
     *      entity="OroEmailBundle:Email",
     *      precision="EDIT"
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
     *      type="entity",
     *      entity="OroEmailBundle:Email",
     *      precision="CREATE"
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
     *      type="action",
     *      group=""
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
     *      type="entity",
     *      entity="OroEmailBundle:Email",
     *      precision="VIEW"
     * )
     * @Template("OroEmailBundle:EmailTemplate:preview.html.twig")
     * @param bool|int $emailTemplateId
     * @return array
     */
    public function previewAction($emailTemplateId = false)
    {
        if (!$emailTemplateId) {
            $emailTemplate = new EmailTemplate();
        } else {
            /** @var EntityManager $em */
            $em = $this->get('doctrine.orm.entity_manager');
            $em->getRepository('Oro\Bundle\EmailBundle\Entity\EmailTemplate')->find($emailTemplateId);
        }

        /** @var FormInterface $form */
        $form = $this->get('oro_email.form.emailtemplate');
        $form->setData($emailTemplate);
        $request = $this->get('request');

        if (in_array($request->getMethod(), array('POST', 'PUT'))) {
            $form->submit($request);
        }

        list ($subjectRendered, $templateRendered) = $this->get('oro_email.email_renderer')
            ->compileMessage($emailTemplate);

        return array(
            'subject' => $subjectRendered,
            'content' => $templateRendered,
        );
    }
}
