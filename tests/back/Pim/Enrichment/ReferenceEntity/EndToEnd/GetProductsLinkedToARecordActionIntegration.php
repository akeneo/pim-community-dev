<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Enrichment\ReferenceEntity\EndToEnd;

use Akeneo\Channel\API\Query\Channel;
use Akeneo\Channel\API\Query\LabelCollection;
use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeRequirement;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSet;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\Test\Integration\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author    Samir Boulil
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class GetProductsLinkedToARecordActionIntegration extends TestCase
{
    private const ATTRIBUTE_LINK = 'link';
    private const RECORD = 'kartell';
    private const REFERENCE_ENTITY = 'brand';

    /** @var Family */
    private $family;

    /** @var AttributeInterface */
    private $sku;

    /** @var AttributeInterface */
    private $attributeLink;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('feature_flags')->enable('reference_entity');
        $this->get('akeneo_referenceentity.infrastructure.persistence.query.channel.find_channels')
            ->setChannels([
                new Channel('ecommerce', ['en_US'], LabelCollection::fromArray(['en_US' => 'Ecommerce', 'de_DE' => 'Ecommerce', 'fr_FR' => 'Ecommerce']), ['USD'])
            ]);
    }

    /**
     * @test
     * @group critical
     */
    public function it_finds_the_products_linked_to_a_specific_record_for_an_attribute()
    {
        $this->loadProductsLinkedToARecord();
        $this->assertRequestContractRespected();
    }

    private function assertRequestContractRespected(): void
    {
        $requestContract = sprintf(
            '%s/src/Akeneo/ReferenceEntity/tests/shared/responses/Record/Product/ok.json',
            $this->getParameter('kernel.project_dir')
        );

        /** @var JsonResponse $response */
        $response = $this->get('pimee.reference_entity.enrichment.product.get_products_linked_to_a_record_action')(
            new Request(['channel' => 'ecommerce', 'locale' => 'en_US']),
            self::RECORD,
            self::ATTRIBUTE_LINK
        );
        $this->assertSameResponses($requestContract, $response->getContent());
    }

    private function assertSameResponses(string $expectedResponse, string $actualResponse): void
    {
        $expectedContent = json_decode(file_get_contents($expectedResponse), true)['response']['body'];
        $actualContent = json_decode($actualResponse, true);
        if (null === $expectedResponse) {
            throw new \RuntimeException(
                sprintf('Impossible to load "%s" file, the file is not be present or is malformed', $expectedResponse)
            );
        }
        self::assertEquals(
            $expectedContent,
            $actualContent,
            'Expected response content is not the same as the actual.'
        );
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function loadProductsLinkedToARecord(): void
    {
        $this
            // Reference entities
            ->withReferenceEntity(self::REFERENCE_ENTITY)
            ->withRecords(self::RECORD)
            // Products
            ->withAttributeLinkToRecord([
                    'code'             => self::ATTRIBUTE_LINK,
                    'type'             => ReferenceEntityType::REFERENCE_ENTITY,
                    'reference_entity' => self::REFERENCE_ENTITY
                ])
            ->withProductFamily('accessories')
            ->withLinkedProductModel('model-braided-hat')
            ->withLinkedProduct('1111111304');
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    private function withReferenceEntity(string $identifier): self
    {
        $referenceEntityRepository = $this->get(
            'akeneo_referenceentity.infrastructure.persistence.repository.reference_entity'
        );
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($identifier);
        $referenceEntityRepository->create(
            ReferenceEntity::create(
                $referenceEntityIdentifier,
                [],
                Image::createEmpty()
            )
        );

        return $this;
    }

    private function withRecords(string ...$records): self
    {
        $identifier = ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY);
        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');

        foreach ($records as $record) {
            $recordRepository->create(
                Record::create(
                    RecordIdentifier::fromString($record),
                    $identifier,
                    RecordCode::fromString($record),
                    ValueCollection::fromValues([])
                )
            );
        }

        return $this;
    }

    private function withAttributeLinkToRecord(array $attributesData): self
    {
        $data = [
            'code'        => $attributesData['code'],
            'type'        => $attributesData['type'],
            'localizable' => false,
            'scopable'    => false,
            'group'       => 'other',
        ];
        /** @var AttributeInterface $attribute */
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $attribute->setProperty('reference_data_name', $attributesData['reference_entity']);
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);

        $constraints = $this->get('validator')->validate($attribute);
        self::assertCount(0, $constraints);

        $this->get('pim_catalog.saver.attribute')->save($attribute);
        $this->attributeLink = $attribute;

        return $this;
    }

    private function withProductFamily(string $familyCode): self
    {
        $this->sku = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('sku');
        $this->family = new Family();
        $this->family->setCode($familyCode);
        $this->family->addAttribute($this->sku);
        $this->family->addAttribute($this->attributeLink);
        $this->family->setAttributeAsLabel($this->sku);

        $skuRequired = new AttributeRequirement();
        $skuRequired->setAttribute($this->sku);
        $skuRequired->setRequired(true);
        $skuRequired->setChannel($this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce'));
        $this->family->setAttributeRequirements([$skuRequired]);

        $constraints = $this->get('validator')->validate($this->family);
        self::assertCount(0, $constraints);

        $this->get('pim_catalog.saver.family')->save($this->family);

        return $this;
    }

    private function withLinkedProduct(string $identifier): self
    {
        /** @var WriteValueCollectionFactory $valueCollectionFactory */
        $valueCollectionFactory = $this->get('akeneo.pim.enrichment.factory.write_value_collection');
        $valueCollection = $valueCollectionFactory->createFromStorageFormat(
            [
                $this->sku->getCode()           => [
                    '<all_channels>' => [
                        '<all_locales>' => $identifier
                    ]
                ],
                $this->attributeLink->getCode() => [
                    '<all_channels>' => [
                        '<all_locales>' => self::RECORD
                    ]
                ],
            ]
        );

        $product = new Product();
        $product->setValues($valueCollection);
        $product->setIdentifier($product->getValue($this->sku->getCode())->getData());

        $constraints = $this->get('validator')->validate($product);
        self::assertCount(0, $constraints);

        $this->get('pim_catalog.saver.product')->save($product);

        return $this;
    }

    private function withLinkedProductModel(string $code): self
    {
        $familyVariant = new FamilyVariant();
        $familyVariant->setCode('family_variant');
        $familyVariant->setFamily($this->family);

        $variantAttributeSet = new VariantAttributeSet();
        $variantAttributeSet->setLevel(1);
        $variantAttributeSet->setAttributes([$this->attributeLink, $this->sku]);
        $variantAttributeSet->setAxes([$this->attributeLink]);
        $familyVariant->addVariantAttributeSet($variantAttributeSet);

        $constraints = $this->get('validator')->validate($familyVariant);
        self::assertCount(0, $constraints);

        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);

        /** @var WriteValueCollectionFactory $valueCollectionFactory */
        $valueCollectionFactory = $this->get('akeneo.pim.enrichment.factory.write_value_collection');
        $valueCollection = $valueCollectionFactory->createFromStorageFormat(
            [
                $this->attributeLink->getCode() => [
                    '<all_channels>' => [
                        '<all_locales>' => self::RECORD
                    ]
                ],
            ]
        );

        $productModel = new ProductModel();
        $productModel->setCode($code);
        $productModel->setValues($valueCollection);
        $productModel->setFamilyVariant($familyVariant);

        $constraints = $this->get('validator')->validate($productModel);
//        self::assertCount(0, $constraints);

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $this;
    }

}
