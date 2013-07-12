<?php

namespace Oro\Bundle\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\NotificationBundle\Entity\EmailNotification;
use Oro\Bundle\NotificationBundle\Datagrid\EmailNotificationDatagridManager;

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
     * @Route("/view/{id}", requirements={"id"="\d+"})
     * @Template()
     */
    public function viewAction()
    {
        return array();
    }

    /**
     * @Route("/update/{id}", requirements={"id"="\d+"}, defaults={"id"=0}))
     * @Template()
     */
    public function updateAction()
    {
        return array();
    }

    /**
     * @Route("/create")
     * @Template("OroNotificationBundle:EmailNotification:update.html.twig")
     */
    public function createAction()
    {
        return $this->updateAction(new EmailNotification());
    }
}
