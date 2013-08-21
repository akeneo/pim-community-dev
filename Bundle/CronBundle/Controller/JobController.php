<?php

namespace Oro\Bundle\CronBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\JobQueueBundle\Entity\Job;

/**
 * @Route("/job")
 */
class JobController extends Controller
{
    /** @DI\Inject("%jms_job_queue.statistics%") */
    protected $statisticsEnabled;

    /**
     * @Route(
     *      "/{page}/{limit}",
     *      name="oro_cron_job_index",
     *      requirements={"page"="\d+", "limit"="\d+"},
     *      defaults={"page"=1, "limit"=20}
     * )
     * @Template
     */
    public function indexAction($page, $limit)
    {
        return array(
            'pid'   => $this->get('oro_cron.job_daemon')->getPid(),
            'pager' => $this->get('knp_paginator')->paginate(
                $this->get('oro_cron.job_manager')->getListQuery(),
                (int) $page,
                (int) $limit
            ),
        );
    }

    /**
     * @Route("/view/{id}", name="oro_cron_job_view", requirements={"id"="\d+"})
     * @Template
     */
    public function viewAction(Job $job)
    {
        $manager    = $this->get('oro_cron.job_manager');
        $statistics = $this->statisticsEnabled
            ? $manager->getJobStatistics($job)
            : array();

        return array(
            'job'             => $job,
            'pid'             => $this->get('oro_cron.job_daemon')->getPid(),
            'relatedEntities' => $manager->getRelatedEntities($job),
            'statistics'      => $statistics,
            'dependencies'    => $this->getDoctrine()
                ->getRepository('JMSJobQueueBundle:Job')
                ->getIncomingDependencies($job),
        );
    }

    /**
     * @Route("/run-daemon", name="oro_cron_job_run_daemon")
     */
    public function runDaemonAction()
    {
        $daemon = $this->get('oro_cron.job_daemon');
        $ret    = array('error' => 1);

        try {
            if ($pid = $daemon->run()) {
                $ret['error']   = 0;
                $ret['message'] = $pid;
            } else {
                $ret['message'] = 'Failed to start daemon';
            }
        } catch (\RuntimeException $e) {
            $ret['message'] = $e->getMessage();
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response(json_encode($ret));
        } else {
            if ($ret['error']) {
                $this->get('session')->getFlashBag()->add('error', $ret['message']);
            } else {
                $this->get('session')->getFlashBag()->add('success', 'Daemon started');
            }

            return $this->redirect($this->generateUrl('oro_cron_job_index'));
        }
    }

    /**
     * @Route("/stop-daemon", name="oro_cron_job_stop_daemon")
     */
    public function stopDaemonAction()
    {
        $daemon = $this->get('oro_cron.job_daemon');
        $ret    = array('error' => 1);

        try {
            if ($daemon->stop()) {
                $ret['error']   = 0;
                $ret['message'] = 'Daemon stopped';
            } else {
                $ret['message'] = 'Failed to stop daemon';
            }
        } catch (\RuntimeException $e) {
            $ret['message'] = $e->getMessage();
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response(json_encode($ret));
        } else {
            $this->get('session')->getFlashBag()->add($ret['error'] ? 'error' : 'success', $ret['message']);

            return $this->redirect($this->generateUrl('oro_cron_job_index'));
        }
    }

    /**
     * @Route("/status", name="oro_cron_job_status")
     */
    public function statusAction()
    {
        return $this->getRequest()->isXmlHttpRequest()
            ? new Response($this->get('oro_cron.job_daemon')->getPid())
            : $this->redirect($this->generateUrl('oro_cron_job_index'));
    }
}
