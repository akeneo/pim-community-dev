<?php

namespace PimEnterprise\Behat\Context;

use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Behat\Behat\Context\Step;
use Pim\Behat\Context\JobContext as BaseJobContext;

class JobContext extends BaseJobContext
{
    /**
     * @param JobInstance $job
     *
     * @Given /^I should be on the last ("([^"]*)" (import|export) job) page$/
     */
    public function iShouldBeOnTheJobExecutionPage(JobInstance $job)
    {
        $jobPage           = sprintf('%s show', ucfirst($job->getType()));
        $jobExecutionId    = $job->getJobExecutions()->last()->getId();
        $expectedAddress   = $this->getPage($jobPage)->getUrl(['id' => $jobExecutionId]);
        $this->getMainContext()->getSubcontext('navigation')->assertAddress($expectedAddress);
    }
}
