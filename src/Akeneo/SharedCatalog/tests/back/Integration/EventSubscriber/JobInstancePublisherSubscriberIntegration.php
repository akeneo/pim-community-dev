<?php

namespace Akeneo\SharedCatalog\tests\back\Integration\EventSubscriber;

use Akeneo\SharedCatalog\tests\back\Utils\AuthenticateAs;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Doctrine\ORM\EntityManager;

class JobInstancePublisherSubscriberIntegration extends TestCase
{
    use AuthenticateAs;

    /** @var EntityManager */
    private $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = $this->get('doctrine')->getManager();
        $this->authenticateAs('admin');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @test
     */
    public function it_adds_the_current_user_as_publisher_on_shared_catalog()
    {
        $jobInstance = new JobInstance();
        $jobInstance->setCode('shared_catalog');
        $jobInstance->setLabel('shared_catalog');
        $jobInstance->setJobName('akeneo_shared_catalog');
        $jobInstance->setStatus(JobInstance::STATUS_READY);
        $jobInstance->setConnector('Some connector name');
        $jobInstance->setType('export');
        $jobInstance->setRawParameters([]);

        $this->em->persist($jobInstance);
        $this->em->flush();

        $actualPublisher = $jobInstance->getRawParameters()['publisher'];
        self::assertEquals('admin@example.com', $actualPublisher);
    }
}
