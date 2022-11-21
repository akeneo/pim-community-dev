<?php


namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception;

final class JobNotFoundException extends \Exception
{
    public function __construct($jobName)
    {
        parent::__construct(sprintf('Job "%s" not found', $jobName));
    }
}
