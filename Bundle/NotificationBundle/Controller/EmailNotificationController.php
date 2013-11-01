<?php

namespace Oro\Bundle\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\NotificationBundle\Entity\EmailNotification;

/**
 * @Route("/email")
 */
class EmailNotificationController extends Controller
{
    /**
     * @Route(
     *      "/{_format}",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Acl(
     *      id="oro_notification_emailnotification_view",
     *      type="entity",
     *      class="OroNotificationBundle:EmailNotification",
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
     *      id="oro_notification_emailnotification_update",
     *      type="entity",
     *      class="OroNotificationBundle:EmailNotification",
     *      permission="EDIT"
     * )
     * @Template()
     */
    public function updateAction(EmailNotification $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route("/create")
     * @Acl(
     *      id="oro_notification_emailnotification_create",
     *      type="entity",
     *      class="OroNotificationBundle:EmailNotification",
     *      permission="CREATE"
     * )
     * @Template("OroNotificationBundle:EmailNotification:update.html.twig")
     */
    public function createAction()
    {
        return $this->update(new EmailNotification());
    }

    /**
     * @param EmailNotification $entity
     * @return array
     */
    protected function update(EmailNotification $entity)
    {
        if ($this->get('oro_notification.form.handler.email_notification')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('oro.notification.controller.emailnotification.saved.message')
            );

            return $this->get('oro_ui.router')->actionRedirect(
                array(
                    'route' => 'oro_notification_emailnotification_update',
                    'parameters' => array('id' => $entity->getId()),
                ),
                array(
                    'route' => 'oro_notification_emailnotification_index',
                )
            );
        }

        return array(
            'form' => $this->get('oro_notification.form.email_notification')->createView(),
        );
    }
}
