<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Model;

use Akeneo\Platform\Bundle\ImportExportBundle\Model\JobExecutionTracking;
use Akeneo\Platform\Bundle\ImportExportBundle\Model\StepExecutionTracking;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionTrackingSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(JobExecutionTracking::class);
    }

    function it_tells_when_it_has_an_error()
    {
        $stepWithoutError = new StepExecutionTracking();
        $stepWithError = new StepExecutionTracking();
        $stepWithError->hasError = true;
        $this->steps = [$stepWithoutError, $stepWithError, $stepWithoutError];

        $this->hasError()->shouldBe(true);
    }

    function it_tells_when_it_has_a_warning()
    {
        $stepWithoutWarning = new StepExecutionTracking();
        $stepWithWarning = new StepExecutionTracking();
        $stepWithWarning->hasWarning = true;

        $this->steps = [$stepWithoutWarning, $stepWithWarning, $stepWithoutWarning];

        $this->hasWarning()->shouldBe(true);
    }
}
