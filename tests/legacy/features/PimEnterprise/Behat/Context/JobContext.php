<?php

namespace PimEnterprise\Behat\Context;

use Akeneo\Component\Batch\Model\JobInstance;
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
        $type = join('', array_map(function ($jobTypeWord) {
            return ucfirst($jobTypeWord);
        }, explode('_', $job->getType())));
        $jobPage           = sprintf('%s show', $type);
        $jobExecutionId    = $job->getJobExecutions()->last()->getId();
        $expectedAddress   = $this->getPage($jobPage)->getUrl(['id' => $jobExecutionId]);
        $this->getMainContext()->getSubcontext('navigation')->assertAddress($expectedAddress);
    }
}
