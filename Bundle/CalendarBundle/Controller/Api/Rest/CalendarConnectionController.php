<?php

namespace Oro\Bundle\CalendarBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\Rest\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\SoapBundle\Controller\Api\EntityManagerAwareInterface;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\CalendarBundle\Entity\Repository\CalendarRepository;
use Oro\Bundle\CalendarBundle\Entity\Repository\CalendarConnectionRepository;
use Oro\Bundle\CalendarBundle\Entity\Calendar;
use Oro\Bundle\CalendarBundle\Entity\CalendarConnection;

/**
 * @RouteResource("calendar")
 * @NamePrefix("oro_api_")
 */
class CalendarConnectionController extends FOSRestController implements
    EntityManagerAwareInterface,
    ClassResourceInterface
{
    protected $userNameFormat = null;

    /**
     * Get calendar connections.
     *
     * @param int $id Calendar id
     *
     * @Get(name="oro_api_get_calendar_connections", requirements={"id"="\d+"})
     * @ApiDoc(
     *      description="Get calendar connections",
     *      resource=true
     * )
     * @AclAncestor("oro_calendar_view")
     *
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function getConnectionsAction($id)
    {
        $manager = $this->getManager();
        /** @var CalendarConnectionRepository $repo */
        $repo = $manager->getRepository();
        $qb   = $repo->createQueryBuilder('a')
            ->select(
                'a.color, a.backgroundColor'
                . ', ac.id as calendar, ac.name as calendarName'
                . ', u.id as owner, u.firstName as ownerFirstName, u.lastName as ownerLastName'
            )
            ->innerJoin('a.calendar', 'c')
            ->innerJoin('a.connectedCalendar', 'ac')
            ->innerJoin('ac.owner', 'u')
            ->where('c.id = :id')
            ->orderBy('a.createdAt')
            ->setParameter('id', $id);

        /** @var SecurityFacade $securityFacade */
        $securityFacade = $this->get('oro_security.security_facade');
        if (!$securityFacade->isGranted('oro_calendar_connection_view')) {
            $qb
                ->andWhere('u.id = :userId')
                ->setParameter('userId', $this->getUser()->getId());
        }

        $result = $qb->getQuery()->getArrayResult();
        foreach ($result as $key => $item) {
            if ($item['calendarName'] === null) {
                $result[$key]['calendarName'] = $this->getOwnerName(
                    $item['ownerFirstName'],
                    $item['ownerLastName']
                );
            }
            unset($result[$key]['ownerFirstName']);
            unset($result[$key]['ownerLastName']);
            // prohibit to remove the current calendar from the list of connected calendar.
            $result[$key]['removable'] = ($item['calendar'] != $id);
        }

        return new Response(json_encode($result), Codes::HTTP_OK);
    }

    /**
     * Create new calendar connection.
     *
     * @param int $id Calendar id
     *
     * @ApiDoc(
     *      description="Create new calendar connection",
     *      resource=true
     * )
     * @AclAncestor("oro_calendar_connection_view")
     *
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function postConnectionsAction($id)
    {
        $calendarId = $this->getRequest()->get('calendar');
        $ownerId    = $this->getRequest()->get('owner');
        if (empty($calendarId) && empty($ownerId)) {
            throw new \InvalidArgumentException('Either "calendar" or "owner" argument must be provided.');
        }

        $manager = $this->getManager();
        /** @var CalendarRepository $calendarRepo */
        $calendarRepo = $manager->getObjectManager()->getRepository('OroCalendarBundle:Calendar');

        /** @var Calendar $calendar */
        $calendar = $calendarRepo->find($id);
        if (!$calendar) {
            return $this->handleView($this->view(null, Codes::HTTP_NOT_FOUND));
        }

        $connectedCalendar = !empty($calendarId)
            ? $calendarRepo->find($id)
            : $calendarRepo->findByUser($ownerId);
        if (!$connectedCalendar) {
            return $this->handleView($this->view(null, Codes::HTTP_NOT_FOUND));
        }

        $connection = new CalendarConnection($connectedCalendar);
        $calendar->addConnection($connection);
        $manager->getObjectManager()->persist($connection);
        $manager->getObjectManager()->flush();

        $data         = array(
            'color'           => $connection->getColor(),
            'backgroundColor' => $connection->getBackgroundColor(),
            'calendar'        => $connectedCalendar->getId(),
            'owner'           => $connectedCalendar->getOwner()->getId(),
            'removable'       => true
        );
        $calendarName = $connectedCalendar->getName();
        if (empty($calendarName)) {
            $calendarName = $this->getOwnerName(
                $connectedCalendar->getOwner()->getFirstName(),
                $connectedCalendar->getOwner()->getLastName()
            );
        }
        $data['calendarName'] = $calendarName;

        $view = $this->view($data, Codes::HTTP_CREATED);

        return $this->handleView($view);
    }

    /**
     * Remove calendar connection.
     *
     * @param int $id          Calendar id
     * @param int $connectedId Connected calendar id
     *
     * @ApiDoc(
     *      description="Remove calendar connection",
     *      resource=true
     * )
     * @AclAncestor("oro_calendar_connection_view")
     * @return Response
     */
    public function deleteConnectionsAction($id, $connectedId)
    {
        $em = $this->getManager()->getObjectManager();
        /** @var CalendarConnectionRepository $repo */
        $repo = $this->getManager()->getRepository();

        $connection = $repo->findByRelation($id, $connectedId);
        if (!$connection) {
            return $this->handleView($this->view(null, Codes::HTTP_NOT_FOUND));
        }

        $em->remove($connection);
        $em->flush();

        return $this->handleView($this->view(null, Codes::HTTP_NO_CONTENT));
    }

    /**
     * @return ApiEntityManager
     */
    public function getManager()
    {
        return $this->get('oro_calendar.calendar_connection.manager.api');
    }

    /**
     * Returns calendar owner name formatted based on system configuration
     *
     * @param string $firstName
     * @param string $lastName
     * @return string
     */
    protected function getOwnerName($firstName, $lastName)
    {
        return str_replace(
            array('%first%', '%last%'),
            array($firstName, $lastName),
            $this->getUserNameFormat()
        );
    }

    /**
     * Gets a string used to format calendar owner name
     *
     * @return string
     */
    protected function getUserNameFormat()
    {
        if ($this->userNameFormat === null) {
            $this->userNameFormat = $this->get('oro_config.twig.config_extension')
                ->getUserValue('oro_locale.name_format');
        }

        return $this->userNameFormat;
    }
}
