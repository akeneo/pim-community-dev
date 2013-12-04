<?php

namespace Oro\Bundle\CronBundle\Entity\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Util\ClassUtils;

use JMS\JobQueueBundle\Entity\Job;

class JobManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Returns basic query instance to get collection with all job instances
     *
     * @return QueryBuilder
     */
    public function getListQuery()
    {
        $qb = $this->em->createQueryBuilder();

        return $qb
            ->select('j')
            ->from('JMSJobQueueBundle:Job', 'j')
            ->where($qb->expr()->isNull('j.originalJob'))
            ->orderBy('j.createdAt', 'DESC');
    }

    public function getRelatedEntities(Job $job)
    {
        $related = array();

        foreach ($job->getRelatedEntities() as $entity) {
            $class = ClassUtils::getClass($entity);

            $related[] = array(
                'class' => $class,
                'id'    => json_encode($this->em->getClassMetadata($class)->getIdentifierValues($entity)),
                'raw'   => $entity,
            );
        }

        return $related;
    }

    public function getJobStatistics(Job $job)
    {
        $statisticData         = array();
        $dataPerCharacteristic = array();
        $statistics            = $this->em->getConnection()
            ->query("SELECT * FROM jms_job_statistics WHERE job_id = " . $job->getId());

        foreach ($statistics as $row) {
            $dataPerCharacteristic[$row['characteristic']][] = array(
                $row['createdAt'],
                $row['charValue'],
            );
        }

        if ($dataPerCharacteristic) {
            $statisticData = array(array_merge(array('Time'), $chars = array_keys($dataPerCharacteristic)));
            $startTime     = strtotime($dataPerCharacteristic[$chars[0]][0][0]);
            $endTime       = strtotime(
                $dataPerCharacteristic[$chars[0]][count($dataPerCharacteristic[$chars[0]])-1][0]
            );
            $scaleFactor   = $endTime - $startTime > 300 ? 1/60 : 1;

            // This assumes that we have the same number of rows for each characteristic.
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

        return $statisticData;
    }
}
