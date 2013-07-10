<?php                                                                           
namespace Pim\Bundle\BatchBundle\Tests\Unit\Job;

use Pim\Bundle\BatchBundle\Job\StepExecution;;

/**
 * Tests related to the StepExecution class
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class StepExecutionTest extends \PHPUnit_Framework_TestCase
{
    /* @var StepExecution $execution */
    private StepExecution execution = null;


    private StepExecution blankExecution = newStepExecution(new StepSupport("blank"), null);

    private ExecutionContext foobarEc = new ExecutionContext();

    public function __construct()
    {
        parent::__construct();
        $this->execution = :newStepExecution(new StepSupport("stepName"), new Long(23));


}
