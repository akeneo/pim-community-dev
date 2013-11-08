<?php

namespace Oro\Bundle\CalendarBundle\Controller;

use Oro\Bundle\CalendarBundle\Provider\CalendarDateTimeConfigProvider;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\CalendarBundle\Entity\Calendar;
use Oro\Bundle\CalendarBundle\Entity\Repository\CalendarRepository;

class CalendarController extends Controller
{
    /**
     * View user's default calendar
     *
     * @Route("/default", name="oro_calendar_view_default")
     * @AclAncestor("oro_calendar_view")
     */
    public function viewDefaultAction()
    {
        /** @var User $user */
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        /** @var CalendarRepository $repo */
        $repo     = $em->getRepository('OroCalendarBundle:Calendar');
        $calendar = $repo->findByUser($user->getId());

        return $this->forward(
            'OroCalendarBundle:Calendar:view',
            array('calendar' => $calendar)
        );
    }

    /**
     * View calendar
     *
     * @Route("/view/{id}", name="oro_calendar_view", requirements={"id"="\d+"})
     *
     * @Template
     * @Acl(
     *      id="oro_calendar_view",
     *      type="entity",
     *      class="OroCalendarBundle:Calendar",
     *      permission="VIEW",
     *      group_name=""
     * )
     */
    public function viewAction(Calendar $calendar)
    {
        /** @var SecurityFacade $securityFacade */
        $securityFacade = $this->get('oro_security.security_facade');
        /** @var CalendarDateTimeConfigProvider $calendarConfigProvider */
        $calendarConfigProvider = $this->get('oro_calendar.provider.calendar_config');

        $currentDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $dateRange = $calendarConfigProvider->getDateRange($currentDate);

        $result = array(
            'event_form' => $this->get('oro_calendar.calendar_event.form')->createView(),
            'user_select_form' => $this->get('form.factory')
                ->createNamed(
                    'new_calendar_owner',
                    'oro_user_select',
                    null,
                    array(
                        'required' => true,
                        'configs'  => array(
                            'placeholder' => 'oro.calendar.form.choose_user_to_add_calendar',
                            /* @todo: Must be removed. I have to do this because oro_user_select sets 400px */
                            'width' => 'off'
                        )
                    )
                )
                ->createView(),
            'entity' => $calendar,
            'calendar' => array(
                'selectable' => $securityFacade->isGranted('oro_calendar_event_create'),
                'editable' => $securityFacade->isGranted('oro_calendar_event_update'),
                'removable' => $securityFacade->isGranted('oro_calendar_event_delete'),
                'timezoneOffset' => $calendarConfigProvider->getTimezoneOffset($currentDate)
            ),
            'startDate' => $dateRange['startDate'],
            'endDate' => $dateRange['endDate'],
        );

        return $result;
    }
}
