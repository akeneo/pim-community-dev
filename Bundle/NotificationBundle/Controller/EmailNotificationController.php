<?php

namespace Oro\Bundle\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\NotificationBundle\Entity\EmailNotification;
use Oro\Bundle\NotificationBundle\Datagrid\EmailNotificationDatagridManager;

/**
 * @Route("/email")
 * @Acl(
 *      id="oro_notification_emailnotification",
 *      name="Transactional emails",
 *      description="Notification rules manipulation",
 *      parent="root"
 * )
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
     *      id="oro_user_user_view",
     *      name="View List of notification rules",
     *      description="View notification rules list",
     *      parent="oro_notification_emailnotification"
     * )
     * @Template()
     */
    public function indexAction()
    {
        /** @var EmailNotificationDatagridManager $gridManager */
        $gridManager = $this->get('oro_navigation.emailnotification.datagrid_manager');
        $datagridView = $gridManager->getDatagrid()->createView();

        if ('json' == $this->getRequest()->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
        }

        return array('datagrid' => $datagridView);
    }

    /**
     * @Route("/update/{id}", requirements={"id"="\d+"}, defaults={"id"=0}))
     * @Acl(
     *      id="oro_notification_emailnotification_update",
     *      name="Edit notification rule",
     *      description="Edit notification rule",
     *      parent="oro_notification_emailnotification"
     * )
     * @Template()
     */
    public function updateAction()
    {
        return array();
    }

    /**
     * @Route("/create")
     * @Acl(
     *      id="oro_notification_emailnotification_create",
     *      name="Create notification rule",
     *      description="Create notification rule",
     *      parent="oro_notification_emailnotification"
     * )
     * @Template("OroNotificationBundle:EmailNotification:update.html.twig")
     */
    public function createAction()
    {
        return $this->updateAction(new EmailNotification());
    }
}
