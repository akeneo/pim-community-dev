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

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\CalendarBundle\Entity\Repository\CalendarEventRepository;
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
     *      name="calendar", requirements="\d+", nullable=false,
     *      description="Calendar id.")
     * @QueryParam(
     *      name="start",
     *      requirements="\d{4}(-\d{2}(-\d{2}([T ]\d{2}:\d{2}(:\d{2}(\.\d+)?)?(Z|([-+]\d{2}(:?\d{2})?))?)?)?)?",
     *      nullable=false,
     *      description="Start date in ISO8601. For example: 2009-11-05T13:15:30Z.")
     * @QueryParam(
     *      name="end",
     *      requirements="\d{4}(-\d{2}(-\d{2}([T ]\d{2}:\d{2}(:\d{2}(\.\d+)?)?(Z|([-+]\d{2}(:?\d{2})?))?)?)?)?",
     *      nullable=false,
     *      description="End date in ISO8601. For example: 2009-11-05T13:15:30Z.")
     * @QueryParam(
     *      name="subordinate", requirements="[01]", nullable=true,
     *      description="Determine whether events from attached calendars should be included ou not. Defaults to 0.")
     * @ApiDoc(
     *      description="Get calendar events",
     *      resource=true
     * )
     * @AclAncestor("oro_calendar_view")
     *
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function cgetAction()
    {
        $calendarId = $this->getRequest()->get('calendar');
        if (empty($calendarId)) {
            throw new \InvalidArgumentException('The "calendar" argument must be provided.');
        }
        $start = $this->getRequest()->get('start');
        if (empty($start)) {
            throw new \InvalidArgumentException('The "start" argument must be provided.');
        }
        $end = $this->getRequest()->get('end');
        if (empty($end)) {
            throw new \InvalidArgumentException('The "end" argument must be provided.');
        }

        $calendarId  = (int)$calendarId;
        $start       = new \DateTime($start);
        $end         = new \DateTime($end);
        $subordinate = (bool)$this->getRequest()->get('subordinate', false);

        $manager = $this->getManager();
        /** @var CalendarEventRepository $repo */
        $repo = $manager->getRepository();
        $qb   = $repo->getEventsQueryBuilder($calendarId, $start, $end, $subordinate);
        $qb->select('c.id as calendar, e.id, e.title, e.start, e.end, e.allDay, e.reminder');

        $result = array();
        foreach ($qb->getQuery()->getArrayResult() as $item) {
            $resultItem = array();
            foreach ($item as $field => $value) {
                $this->transformEntityField($field, $value);
                $resultItem[$field] = $value;
            }
            $result[] = $resultItem;
        }

        return new Response(json_encode($result), Codes::HTTP_OK);
    }

    /**
     * Get calendar event.
     *
     * @param string $id Calendar event id
     *
     * @ApiDoc(
     *      description="Get calendar event",
     *      resource=true
     * )
     * @AclAncestor("oro_calendar_view")
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
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
     * @AclAncestor("oro_calendar_update")
     *
     * @return Response
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Create new calendar event.
     *
     * @Post("calendarevents")
     * @ApiDoc(
     *      description="Create new calendar event",
     *      resource=true
     * )
     * @AclAncestor("oro_calendar_create")
     *
     * @return Response
     */
    public function postAction()
    {
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
     * @AclAncestor("oro_calendar_delete")
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
