<?php

namespace Oro\Bundle\EmailBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
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
     *      type="entity",
     *      class="OroEmailBundle:EmailTemplate",
     *      permission="VIEW"
     * )
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/update/{id}", requirements={"id"="\d+"}, defaults={"id"=0}))
     * @Acl(
     *      id="oro_email_emailtemplate_update",
     *      type="entity",
     *      class="OroEmailBundle:EmailTemplate",
     *      permission="EDIT"
     * )
     * @Template()
     */
    public function updateAction(EmailTemplate $entity, $isClone = false)
    {
        return $this->update($entity, $isClone);
    }

    /**
     * @Route("/create")
     * @Acl(
     *      id="oro_email_emailtemplate_create",
     *      type="entity",
     *      class="OroEmailBundle:EmailTemplate",
     *      permission="CREATE"
     * )
     * @Template("OroEmailBundle:EmailTemplate:update.html.twig")
     */
    public function createAction()
    {
        return $this->update(new EmailTemplate());
    }

    /**
     * @Route("/clone/{id}", requirements={"id"="\d+"}, defaults={"id"=0}))
     * @AclAncestor("oro_email_emailtemplate_create")
     * @Template("OroEmailBundle:EmailTemplate:update.html.twig")
     */
    public function cloneAction(EmailTemplate $entity)
    {
        return $this->update(clone $entity, true);
    }

    /**
     * @Route("/preview/{id}", requirements={"id"="\d+"}, defaults={"id"=0}))
     * @Acl(
     *      id="oro_email_emailtemplate_preview",
     *      type="entity",
     *      class="OroEmailBundle:EmailTemplate",
     *      permission="VIEW"
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

        $templateRendered = $this->get('oro_email.email_renderer')
            ->compilePreview($emailTemplate);

        return array(
            'content' => $templateRendered,
        );
    }

    /**
     * @param EmailTemplate $entity
     * @param bool $isClone
     * @return array
     */
    protected function update(EmailTemplate $entity, $isClone = false)
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
}
