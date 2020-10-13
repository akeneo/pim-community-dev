<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Query;

use Akeneo\Platform\Bundle\ImportExportBundle\Model\JobExecutionTracking;
use Akeneo\Platform\Bundle\ImportExportBundle\Model\StepExecutionTracking;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetJobExecutionTracking
{

    public function execute(int $jobExecutionId): JobExecutionTracking
    {
        $expectedJobExecutionTracking = new JobExecutionTracking();
        $expectedJobExecutionTracking->status = 'IN PROGRESS';
        $expectedJobExecutionTracking->currentStep = 2;
        $expectedJobExecutionTracking->totalSteps = 3;

        $expectedStepExecutionTracking1 = new StepExecutionTracking();
        $expectedStepExecutionTracking1->isTrackable = false;
        $expectedStepExecutionTracking1->name = 'validation';
        $expectedStepExecutionTracking1->status = 'COMPLETED';
        $expectedStepExecutionTracking1->duration = 5;
        $expectedStepExecutionTracking1->hasError = false;
        $expectedStepExecutionTracking1->hasWarning = true;
        $expectedStepExecutionTracking1->processedItems = 0;
        $expectedStepExecutionTracking1->totalItems = 0;

        $expectedStepExecutionTracking2 = new StepExecutionTracking();
        $expectedStepExecutionTracking2->isTrackable = true;
        $expectedStepExecutionTracking2->name = 'import';
        $expectedStepExecutionTracking2->status = 'IN PROGRESS';
//        $expectedStepExecutionTracking2->duration = 0;
        $expectedStepExecutionTracking2->hasError = false;
        $expectedStepExecutionTracking2->hasWarning = false;
        $expectedStepExecutionTracking2->processedItems = 10;
        $expectedStepExecutionTracking2->totalItems = 100;

        $expectedStepExecutionTracking3 = new StepExecutionTracking();
        $expectedStepExecutionTracking3->isTrackable = true;
        $expectedStepExecutionTracking3->name = 'import_associations';
        $expectedStepExecutionTracking3->status = 'NOT STARTED';
        $expectedStepExecutionTracking2->duration = 0;
        $expectedStepExecutionTracking3->hasError = false;
        $expectedStepExecutionTracking3->hasWarning = false;
        $expectedStepExecutionTracking3->processedItems = 0;
        $expectedStepExecutionTracking3->totalItems = 0;

        $expectedJobExecutionTracking->steps = [
            $expectedStepExecutionTracking1,
            $expectedStepExecutionTracking2
        ];

        return $expectedJobExecutionTracking;
    }
}
