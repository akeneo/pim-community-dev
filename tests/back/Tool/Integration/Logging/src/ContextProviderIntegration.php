<?php


namespace AkeneoTest\Tool\Integration\Logging;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\LoggingBundle\Domain\Service\ContextProvider;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ContextProviderIntegration extends TestCase
{

    private ContextProvider $contextProvider;
    private JobInstanceRepository $jobInstanceRepository;

    protected function getConfiguration()
    {
        // TODO: Implement getConfiguration() method.
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->contextProvider = $this->get(ContextProvider::class);
        $this->jobInstanceRepository = $this->get('akeneo_batch.job.job_instance_repository');
    }


    public function  test_it_can_produce_context_on_job_instance_entity() {
        $jobInstance=$this->jobInstanceRepository->find("1");
        Assert::assertEquals(['job_instance'=>['id' => 1]], $this->contextProvider->mapEntity2LogContext($jobInstance));
    }


    public function  test_it_can_not_produce_context_on_job_instance_entity() {
        $object = $this;
        Assert::assertNull($this->contextProvider->mapEntity2LogContext($object));
    }


}