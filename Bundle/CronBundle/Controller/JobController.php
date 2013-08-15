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
        $em     = $this->getDoctrine()->getManagerForClass('JMSJobQueueBundle:Job');
        $failed = $em->getRepository('JMSJobQueueBundle:Job')->findLastJobsWithError(5);
        $query  = $em->createQueryBuilder();

        $query
            ->select('j')
            ->from('JMSJobQueueBundle:Job', 'j')
            ->where($query->expr()->isNull('j.originalJob'))
            ->orderBy('j.id', 'desc');

        foreach ($failed as $i => $job) {
            $query
                ->andWhere($query->expr()->neq('j.id', '?' . $i))
                ->setParameter($i, $job->getId());
        }

        return array(
            'failed' => $failed,
            'pid'    => $this->get('oro_cron.job_daemon')->getPid(),
            'pager'  => $this->get('knp_paginator')->paginate($query, $page, $limit),
        );
    }

    /**
     * @Route("/view/{id}", name="oro_cron_job_view", requirements={"id"="\d+"})
     * @Template
     */
    public function viewAction(Job $job)
    {
        $relatedEntities = array();

        foreach ($job->getRelatedEntities() as $entity) {
            $class = \Doctrine\Common\Util\ClassUtils::getClass($entity);
            $relatedEntities[] = array(
                'class' => $class,
                'raw'   => $entity,
                'id'    => json_encode(
                    $this->getDoctrine()
                        ->getManagerForClass($class)
                        ->getClassMetadata($class)
                        ->getIdentifierValues($entity)
                ),
            );
        }

        $statisticData = $statisticOptions = array();

        if ($this->statisticsEnabled) {
            $dataPerCharacteristic = array();
            foreach ($this->getDoctrine()->getManagerForClass('JMSJobQueueBundle:Job')->getConnection()->query(
                "SELECT * FROM jms_job_statistics WHERE job_id = ".$job->getId()
            ) as $row) {
                $dataPerCharacteristic[$row['characteristic']][] = array(
                    $row['createdAt'],
                    $row['charValue'],
                );
            }

            if ($dataPerCharacteristic) {
                $statisticData = array(array_merge(array('Time'), $chars = array_keys($dataPerCharacteristic)));
                $startTime     = strtotime($dataPerCharacteristic[$chars[0]][0][0]);
                $endTime       = strtotime($dataPerCharacteristic[$chars[0]][count($dataPerCharacteristic[$chars[0]])-1][0]);
                $scaleFactor   = $endTime - $startTime > 300 ? 1/60 : 1;

                // this assumes that we have the same number of rows for each characteristic.
                for ($i = 0, $c = count(reset($dataPerCharacteristic)); $i < $c; $i++) {
                    $row = array((strtotime($dataPerCharacteristic[$chars[0]][$i][0]) - $startTime) * $scaleFactor);

                    foreach ($chars as $name) {
                        $value = (float) $dataPerCharacteristic[$name][$i][1];

                        switch ($name) {
                            case 'memory':
                                $value /= 1024 * 1024;

                                break;
                        }

                        $row[] = $value;
                    }

                    $statisticData[] = $row;
                }
            }
        }

        return array(
            'job'                  => $job,
            'pid'                  => $this->get('oro_cron.job_daemon')->getPid(),
            'relatedEntities'      => $relatedEntities,
            'statisticData'        => $statisticData,
            'statisticOptions'     => $statisticOptions,
            'incomingDependencies' => $this->getDoctrine()
                ->getManagerForClass('JMSJobQueueBundle:Job')
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
