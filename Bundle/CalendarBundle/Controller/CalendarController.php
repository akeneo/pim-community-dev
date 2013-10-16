<?php

namespace Oro\Bundle\CalendarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\CalendarBundle\Entity\Calendar;
use Oro\Bundle\CalendarBundle\Entity\Repository\CalendarRepository;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\SecurityBundle\SecurityFacade;

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
        if (!$calendar) {
            // create default user's calendar if it does not exists yet
            $calendar = new Calendar();
            $calendar->setOwner($user);
            $em->persist($calendar);
            $em->flush();
        }

        return $this->forward(
            'OroCalendarBundle:Calendar:view',
            array('calendar' => $calendar)
        );
    }

    /**
     * @Route("/view/{id}", name="oro_calendar_view", requirements={"id"="\d+"})
     *
     * @Template
     * @AclAncestor("oro_calendar_view")
     */
    public function viewAction(Calendar $calendar)
    {
        /** @var ConfigManager $cm */
        $cm         = $this->get('oro_config.global');
        $locale     = $cm->get('oro_locale.locale');
        $dateFormat = $cm->get('oro_locale.date_format');
        $timeFormat = $cm->get('oro_locale.time_format');
        $timezone   = $cm->get('oro_locale.timezone');
        // @todo: need to be refactored using Intl library
        $firstDay        = 0;
        $monthNames      = array(
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        );
        $monthNamesShort = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
        $dayNames        = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
        $dayNamesShort   = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
        $titleFormat     = array(
            'month' => 'MMMM yyyy',
            'week'  => 'MMM d[ yyyy]{ \'&#8212;\'[ MMM] d yyyy}',
            'day'   => 'dddd, MMM d, yyyy'
        );
        $columnFormat    = array(
            'month' => 'ddd',
            'week'  => 'ddd M/d',
            'day'   => 'dddd M/d'
        );
        $timeFormat      = array(
            '' => 'h(:mm)t'
        );

        $timezoneObj    = new \DateTimeZone($timezone);
        $date           = new \DateTime('now', $timezoneObj);
        $timezoneOffset = $timezoneObj->getOffset($date) / 60;
        $startDate      = clone $date;
        $startDate->setDate($date->format('Y'), $date->format('n'), 1);
        $startDate->setTime(0, 0, 0);
        $startDate->sub(new \DateInterval('P' . ((int)$startDate->format('w') - $firstDay + 7) % 7 . 'D'));
        $endDate = clone $date;
        $endDate->add(new \DateInterval('P1M'));
        $endDate->setTime(0, 0, 0);
        $endDate->add(new \DateInterval('P' . (7 - (int)$endDate->format('w') + $firstDay) % 7 . 'D'));

        /** @var SecurityFacade $cm */
        $securityFacade = $this->get('oro_security.security_facade');
        $editable       =
            $securityFacade->isGranted('oro_calendar_update')
            && $securityFacade->isGranted('oro_calendar_create')
            && $securityFacade->isGranted('oro_calendar_delete');

        return array(
            'form'      => $this->get('oro_calendar.calendar_event.form')->createView(),
            'entity'    => $calendar,
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'calendar'  => array(
                'date'            => $date->format('Y-m-d'),
                'timezoneOffset'  => $timezoneOffset,
                'firstDay'        => $firstDay,
                'monthNames'      => $monthNames,
                'monthNamesShort' => $monthNamesShort,
                'dayNames'        => $dayNames,
                'dayNamesShort'   => $dayNamesShort,
                'titleFormat'     => $titleFormat,
                'columnFormat'    => $columnFormat,
                'timeFormat'      => $timeFormat,
                'editable'        => $editable,
                'selectable'      => $editable,
            )
        );
    }
}
