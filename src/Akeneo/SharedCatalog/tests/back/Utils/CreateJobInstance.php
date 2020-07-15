<?php

namespace Akeneo\SharedCatalog\tests\back\Utils;

use Akeneo\Tool\Component\Batch\Model\JobInstance;

trait CreateJobInstance
{
    private function createJobInstance(
        string $code,
        string $jobName,
        string $type,
        int $status,
        array $rawParameters
    ): JobInstance {
        $jobInstance = new JobInstance();
        $jobInstance->setCode($code);
        $jobInstance->setLabel($code);
        $jobInstance->setJobName($jobName);
        $jobInstance->setStatus($status);
        $jobInstance->setConnector('Some connector name');
        $jobInstance->setType($type);
        $jobInstance->setRawParameters($rawParameters);

        $em = $this->get('doctrine')->getManager();

        $em->persist($jobInstance);
        $em->flush();

        return $jobInstance;
    }
}
