<?php

namespace Oro\Bundle\CalendarBundle\Controller\Api\Rest;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\Rest\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\CalendarBundle\Entity\Repository\CalendarRepository;
use Oro\Bundle\CalendarBundle\Entity\Repository\CalendarEventRepository;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

/**
 * @RouteResource("calendarevent")
 * @NamePrefix("oro_api_")
 */
class CalendarEventController extends RestController implements ClassResourceInterface
{
    /**
     * Get calendar events.
     *
     * @QueryParam(
     *      name="calendar", requirements="\d+", nullable=false, strict=true,
     *      description="Calendar id.")
     * @QueryParam(
     *      name="start",
     *      requirements="\d{4}(-\d{2}(-\d{2}([T ]\d{2}:\d{2}(:\d{2}(\.\d+)?)?(Z|([-+]\d{2}(:?\d{2})?))?)?)?)?",
     *      nullable=false, strict=true,
     *      description="Start date in RFC 3339. For example: 2009-11-05T13:15:30Z.")
     * @QueryParam(
     *      name="end",
     *      requirements="\d{4}(-\d{2}(-\d{2}([T ]\d{2}:\d{2}(:\d{2}(\.\d+)?)?(Z|([-+]\d{2}(:?\d{2})?))?)?)?)?",
     *      nullable=false, strict=true,
     *      description="End date in RFC 3339. For example: 2009-11-05T13:15:30Z.")
     * @QueryParam(
     *      name="subordinate", requirements="(true)|(false)", nullable=true, strict=true, default="false",
     *      description="Determine whether events from connected calendars should be included or not.")
     * @ApiDoc(
     *      description="Get calendar events",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_calendar_event_view",
     *      type="entity",
     *      class="OroCalendarBundle:CalendarEvent",
     *      permission="VIEW",
     *      group_name=""
     * )
     *
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function cgetAction()
    {
        $calendarId  = (int)$this->getRequest()->get('calendar');
        $start       = new \DateTime($this->getRequest()->get('start'));
        $end         = new \DateTime($this->getRequest()->get('end'));
        $subordinate = ('true' === $this->getRequest()->get('subordinate'));

        /** @var SecurityFacade $securityFacade */
        $securityFacade = $this->get('oro_security.security_facade');
        if (!$securityFacade->isGranted('oro_calendar_connection_view')) {
            $subordinate = false;
        }

        $manager = $this->getManager();
        /** @var CalendarEventRepository $repo */
        $repo = $manager->getRepository();
        $qb = $repo->getEventListQueryBuilder($calendarId, $start, $end, $subordinate);

        $result = array();
        foreach ($qb->getQuery()->getArrayResult() as $item) {
            $resultItem = array();
            foreach ($item as $field => $value) {
                $this->transformEntityField($field, $value);
                $resultItem[$field] = $value;
            }
            $resultItem['editable'] =
                ($resultItem['calendar'] === $calendarId)
                && $securityFacade->isGranted('oro_calendar_event_update');
            $resultItem['removable'] =
                ($resultItem['calendar'] === $calendarId)
                && $securityFacade->isGranted('oro_calendar_event_delete');
            $result[] = $resultItem;
        }

        return new Response(json_encode($result), Codes::HTTP_OK);
    }

    /**
     * Update calendar event.
     *
     * @param int $id Calendar event id
     *
     * @ApiDoc(
     *      description="Update calendar event",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_calendar_event_update",
     *      type="entity",
     *      class="OroCalendarBundle:CalendarEvent",
     *      permission="EDIT",
     *      group_name=""
     * )
     *
     * @return Response
     */
    public function putAction($id)
    {
        // remove auxiliary attributes if any
        $this->getRequest()->request->remove('editable');
        $this->getRequest()->request->remove('removable');

        return $this->handleUpdateRequest($id);
    }

    /**
     * Create new calendar event.
     *
     * @Post("calendarevents", name="oro_api_post_calendarevent")
     * @ApiDoc(
     *      description="Create new calendar event",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_calendar_event_create",
     *      type="entity",
     *      class="OroCalendarBundle:CalendarEvent",
     *      permission="CREATE",
     *      group_name=""
     * )
     *
     * @return Response
     */
    public function postAction()
    {
        // remove auxiliary attributes if any
        $this->getRequest()->request->remove('editable');
        $this->getRequest()->request->remove('removable');

        return $this->handleCreateRequest();
    }

    /**
     * Remove calendar event.
     *
     * @param int $id Calendar event id
     *
     * @ApiDoc(
     *      description="Remove calendar event",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_calendar_event_delete",
     *      type="entity",
     *      class="OroCalendarBundle:CalendarEvent",
     *      permission="DELETE",
     *      group_name=""
     * )
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * @return ApiEntityManager
     */
    public function getManager()
    {
        return $this->get('oro_calendar.calendar_event.manager.api');
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->get('oro_calendar.calendar_event.form.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('oro_calendar.calendar_event.form.handler.api');
    }
}
