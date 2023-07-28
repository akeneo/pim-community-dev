<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Structure\Integration\Attribute;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Event\AttributesWereCreatedOrUpdated;
use Akeneo\Pim\Structure\Component\Event\AttributeWasCreated;
use Akeneo\Pim\Structure\Component\Event\AttributeWasUpdated;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Normalizer\Event\AttributesWereCreatedOrUpdatedNormalizer;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Integration\IntegrationTestsBundle\Launcher\PubSubQueueStatus;
use Google\Cloud\PubSub\Message;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributesWereCreatedOrUpdatedMessageIntegration extends TestCase
{
    private PubSubQueueStatus $pubSubQueueStatus;

    protected function setUp(): void
    {
        \putenv('PIM_EDITION=serenity_instance'); // Today we send messages only in serenity. To remove when not needed anymore

        parent::setUp();

        $this->pubSubQueueStatus = $this->get('akeneo_integration_tests.pub_sub_queue_status.test_upsert_attributes_consumer');
        $this->pubSubQueueStatus->flushJobQueue();
        $this->pubSubQueueStatus->createTopicAndSubscription();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->pubSubQueueStatus->flushJobQueue();
    }

    public function test_it_dispatches_message_when_attribute_is_created_or_updated(): void
    {
        self::assertCount(0, $this->pubSubQueueStatus->getMessagesInQueue());
        $this->createAttribute(['code' => 'name', 'labels' => ['en_US' => 'Name', 'fr_FR' => 'Nom']]);
        $messages = $this->pubSubQueueStatus->getMessagesInQueue();
        self::assertCount(1, $messages);
        /** @var Message $message */
        $message = current($messages);
        $event = $this->get(AttributesWereCreatedOrUpdatedNormalizer::class)->denormalize(
            \json_decode($message->data(), true),
            AttributesWereCreatedOrUpdated::class
        );
        self::assertInstanceOf(AttributesWereCreatedOrUpdated::class, $event);
        self::assertInstanceOf(AttributeWasCreated::class, $event->events[0]);
    }

    public function test_it_dispatches_message_when_attributes_are_created_or_updated(): void
    {
        $this->createAttribute(['code' => 'name', 'labels' => ['en_US' => 'Name']]);
        $this->pubSubQueueStatus->flushJobQueue();

        self::assertCount(0, $this->pubSubQueueStatus->getMessagesInQueue());

        /** @var AttributeInterface $updatedAttribute */
        $updatedAttribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('name');
        $this->get('pim_catalog.updater.attribute')->update($updatedAttribute, ['labels' => ['en_US' => 'Name', 'fr_FR' => 'Nom']]);
        $constraintViolations = $this->get('validator')->validate($updatedAttribute);
        self::assertCount(0, $constraintViolations, (string) $constraintViolations);

        /** @var AttributeInterface $updatedAttribute */
        $newAttribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($newAttribute, [
            'code' => 'new',
            'labels' => ['en_US' => 'Name', 'fr_FR' => 'Nom'],
            'type' => AttributeTypes::TEXT,
            'group' => 'other',
        ]);
        $constraintViolations = $this->get('validator')->validate($newAttribute);
        self::assertCount(0, $constraintViolations, (string) $constraintViolations);

        $this->get('pim_catalog.saver.attribute')->saveAll([$updatedAttribute, $newAttribute]);

        $messages = $this->pubSubQueueStatus->getMessagesInQueue();
        self::assertCount(1, $messages);
        /** @var Message $message */
        $message = current($messages);
        $event = $this->get(AttributesWereCreatedOrUpdatedNormalizer::class)->denormalize(
            \json_decode($message->data(), true),
            AttributesWereCreatedOrUpdated::class
        );
        self::assertInstanceOf(AttributesWereCreatedOrUpdated::class, $event);

        self::assertCount(2, $event->events);

        $createdAttributes = \array_filter($event->events, fn ($object) => $object instanceof AttributeWasCreated);
        self::assertCount(1, $createdAttributes);
        $updatedAttributes = \array_filter($event->events, fn ($object) => $object instanceof AttributeWasUpdated);
        self::assertCount(1, $updatedAttributes);

    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @param array<string, mixed> $attributeData
     */
    private function createAttribute(array $attributeData): AttributeInterface
    {
        if (!\array_key_exists('type', $attributeData)) {
            $attributeData['type'] = AttributeTypes::TEXT;
        }
        if (!\array_key_exists('group', $attributeData)) {
            $attributeData['group'] = 'other';
        }

        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $attributeData);
        $constraintViolations = $this->get('validator')->validate($attribute);
        self::assertCount(0, $constraintViolations, (string) $constraintViolations);
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }
}
