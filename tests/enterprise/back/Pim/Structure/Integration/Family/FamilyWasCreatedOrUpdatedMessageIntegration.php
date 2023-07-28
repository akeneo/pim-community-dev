<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Structure\Integration\Family;

use Akeneo\Pim\Structure\Component\Event\FamilyWasCreated;
use Akeneo\Pim\Structure\Component\Event\FamilyWasUpdated;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Normalizer\Event\FamilyWasCreatedNormalizer;
use Akeneo\Pim\Structure\Component\Normalizer\Event\FamilyWasUpdatedNormalizer;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Integration\IntegrationTestsBundle\Launcher\PubSubQueueStatus;
use Google\Cloud\PubSub\Message;
use Webmozart\Assert\Assert;

final class FamilyWasCreatedOrUpdatedMessageIntegration extends TestCase
{
    private PubSubQueueStatus $pubSubQueueStatus;

    protected function setUp(): void
    {
        \putenv('PIM_EDITION=serenity_instance'); // Today we send messages only in serenity. To remove when not needed anymore

        parent::setUp();

        $this->pubSubQueueStatus = $this->get('akeneo_integration_tests.pub_sub_queue_status.dqi_family_evaluate');
        $this->pubSubQueueStatus->flushJobQueue();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->pubSubQueueStatus->flushJobQueue();
    }

    public function test_it_dispatches_message_when_family_is_created(): void
    {
        self::assertCount(0, $this->pubSubQueueStatus->getMessagesInQueue());
        $this->createFamily(['code' => 'shoes', 'labels' => ['en_US' => 'Shoes', 'fr_FR' => 'Chaussures']]);
        $messages = $this->pubSubQueueStatus->getMessagesInQueue();
        self::assertCount(1, $messages);
        /** @var Message $message */
        $message = current($messages);
        $familyWasCreated = $this->get(FamilyWasCreatedNormalizer::class)->denormalize(
            \json_decode($message->data(), true),
            FamilyWasCreated::class
        );
        self::assertInstanceOf(FamilyWasCreated::class, $familyWasCreated);
    }

    public function test_it_dispatches_message_when_family_is_updated(): void
    {
        $this->createFamily(['code' => 'shoes', 'labels' => ['en_US' => 'Shoes']]);
        $this->pubSubQueueStatus->flushJobQueue();

        self::assertCount(0, $this->pubSubQueueStatus->getMessagesInQueue());

        /** @var FamilyInterface $family */
        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('shoes');
        $this->get('pim_catalog.updater.family')->update($family, ['labels' => ['en_US' => 'Shoes', 'fr_FR' => 'Chaussures']]);
        $constraintViolations = $this->get('validator')->validate($family);
        self::assertCount(0, $constraintViolations, (string) $constraintViolations);
        $this->get('pim_catalog.saver.family')->save($family);

        $messages = $this->pubSubQueueStatus->getMessagesInQueue();
        self::assertCount(1, $messages);
        /** @var Message $message */
        $message = current($messages);
        $familyIsUpdated = $this->get(FamilyWasUpdatedNormalizer::class)->denormalize(
            \json_decode($message->data(), true),
            FamilyWasUpdated::class
        );
        self::assertInstanceOf(FamilyWasUpdated::class, $familyIsUpdated);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @param array<string, mixed> $familyData
     */
    private function createFamily(array $familyData): FamilyInterface
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $familyData);
        $constraintViolations = $this->get('validator')->validate($family);
        Assert::count($constraintViolations, 0, (string) $constraintViolations);
        $this->get('pim_catalog.saver.family')->save($family);

        return $family;
    }
}
