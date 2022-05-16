<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Enrichment\ReferenceEntity\Integration\Updater;

use Akeneo\Channel\API\Query\Channel;
use Akeneo\Channel\API\Query\LabelCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceEntityValue;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityCollectionValue;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityCommand;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class ReferenceEntityAttributeCopierIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_copies_a_reference_entity_single_link_value()
    {
        $this->createAttribute([
            'code' => 'designer',
            'group' => 'other',
            'scopable' => false,
            'localizable' => true,
            'type' => ReferenceEntityType::REFERENCE_ENTITY,
            'reference_data_name' => 'designers',
        ]);

        $product = $this->createProduct(
            'some_sku',
            [
                new SetSimpleReferenceEntityValue('designer', null, 'en_US', 'dyson'),
           ]
        );
        Assert::assertnull($product->getValue('designer', 'fr_FR'));

        $this->get('pim_catalog.updater.property_copier')->copyData(
            $product,
            $product,
            'designer',
            'designer',
            [
                'from_locale' => 'en_US',
                'to_locale' => 'fr_FR',
            ]
        );
        Assert::assertInstanceOf(ReferenceEntityValue::class, $product->getValue('designer', 'fr_FR'));
        Assert::assertSame('dyson', $product->getValue('designer', 'fr_FR')->getData()->__toString());
    }

    /**
     * @test
     */
    public function it_copies_a_reference_entity_collection_value()
    {
        $collectionAttribute = $this->createAttribute(
            [
                'code' => 'designers',
                'group' => 'other',
                'scopable' => false,
                'localizable' => true,
                'type' => ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION,
                'reference_data_name' => 'designers',
            ]
        );

        $product = $this->createProduct(
            'some_sku',
            [
                new SetMultiReferenceEntityValue('designers', null, 'en_US', ['dyson', 'starck']),
                new SetMultiReferenceEntityValue('designers', null, 'fr_FR', ['newson'])
            ]
        );
        Assert::assertSame('newson', $product->getValue('designers', 'fr_FR')->__toString());

        $this->get('pim_catalog.updater.property_copier')->copyData(
            $product,
            $product,
            'designers',
            'designers',
            [
                'from_locale' => 'en_US',
                'to_locale' => 'fr_FR',
            ]
        );

        Assert::assertInstanceOf(ReferenceEntityCollectionValue::class, $product->getValue('designers', 'fr_FR'));
        Assert::assertSame('dyson, starck', $product->getValue('designers', 'fr_FR')->__toString());
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createAdminUser();
        $this->loadFixtures();
        $this->get('feature_flags')->enable('reference_entity');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function loadFixtures(): void
    {
        $this->get('akeneo_referenceentity.infrastructure.persistence.query.channel.find_channels')
            ->setChannels([
                new Channel('ecommerce', ['en_US'], LabelCollection::fromArray(['en_US' => 'Ecommerce', 'de_DE' => 'Ecommerce', 'fr_FR' => 'Ecommerce']), ['USD'])
            ]);

        // Enable the fr_FR locale
        // TODO: Remove this part when Channel Service API is used everywhere in Reference Entity queries
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');
        $frFr = $this->get('pim_catalog.repository.locale')->findOneByIdentifier('fr_FR');
        $channel->addLocale($frFr);
        $this->get('pim_catalog.saver.channel')->save($channel);

        // Create a 'designer reference entity with 3 records
        $createReferenceEntityHandler = $this->get('akeneo_referenceentity.application.reference_entity.create_reference_entity_handler');
        ($createReferenceEntityHandler)(new CreateReferenceEntityCommand('designers', []));
        $createRecordHandler = $this->get('akeneo_referenceentity.application.record.create_record_handler');
        ($createRecordHandler)(new CreateRecordCommand('designers', 'starck', []));
        ($createRecordHandler)(new CreateRecordCommand('designers', 'dyson', []));
        ($createRecordHandler)(new CreateRecordCommand('designers', 'newson', []));
    }

    private function createAttribute(array $data): AttributeInterface
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $violations, sprintf('validation failed: %s', $violations));
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    private function createProduct(string $identifier, array $userIntents): ProductInterface
    {
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: $identifier,
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
        $this->clearDoctrineUoW();

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    private function clearDoctrineUoW(): void
    {
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    private function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['username' => $username]);
        $id = $stmt->fetchOne();
        Assert::assertNotNull($id);

        return \intval($id);
    }
}
