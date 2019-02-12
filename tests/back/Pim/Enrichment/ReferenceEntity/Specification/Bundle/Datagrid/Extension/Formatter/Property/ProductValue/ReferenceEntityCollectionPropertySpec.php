<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\Datagrid\Extension\Formatter\Property\ProductValue;

use Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\Datagrid\Extension\Formatter\Property\ProductValue\ReferenceEntityCollectionProperty;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\GetRecordInformationQueryInterface;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\RecordInformation;
use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Oro\Bundle\PimDataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;
use PhpSpec\ObjectBehavior;

/**
 * Datagrid column formatter for a reference entity or a reference entity collection
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class ReferenceEntityCollectionPropertySpec extends ObjectBehavior
{
    function let(
        \Twig_Environment $environment,
        RequestParametersExtractorInterface $paramsExtractor,
        UserContext $userContext,
        GetRecordInformationQueryInterface $getRecordInformationQuery,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($environment, $paramsExtractor, $userContext, $getRecordInformationQuery, $attributeRepository);

        $params = new PropertyConfigurationFake(['name' => 'foo']);
        $this->init($params);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceEntityCollectionProperty::class);
    }

    function it_is_a_property()
    {
        $this->shouldImplement(PropertyInterface::class);
    }

    function it_formats_a_simple_reference_entity_link_value_without_labels(
        ResultRecordInterface $record,
        UserContext $userContext,
        GetRecordInformationQueryInterface $getRecordInformationQuery,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $value = ['data' => 'tony_stark', 'attribute' => 'designer_link'];

        $record->getValue('[values][foo]')->willReturn([$value]);

        $simpleLinkAttribute = new Attribute();
        $simpleLinkAttribute->setType(ReferenceEntityType::REFERENCE_ENTITY)
            ->setBackendType(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION)
            ->setReferenceDataName('designer');
        $attributeRepository->findOneByIdentifier('designer_link')->willReturn($simpleLinkAttribute);

        $recordInformation = new RecordInformation('designer', 'tony_stark', []);
        $getRecordInformationQuery->execute('designer', 'tony_stark')
            ->willReturn($recordInformation);

        $userContext->getCurrentLocaleCode()->willReturn('en_US');

        $this->getValue($record)->shouldReturn('[tony_stark]');
    }

    function it_formats_a_simple_reference_entity_link_value_with_labels(
        ResultRecordInterface $record,
        UserContext $userContext,
        GetRecordInformationQueryInterface $getRecordInformationQuery,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $value = ['data' => 'tony_stark', 'attribute' => 'designer_link'];

        $record->getValue('[values][foo]')->willReturn([$value]);

        $simpleLinkAttribute = new Attribute();
        $simpleLinkAttribute->setType(ReferenceEntityType::REFERENCE_ENTITY)
            ->setBackendType(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION)
            ->setReferenceDataName('designer');
        $attributeRepository->findOneByIdentifier('designer_link')->willReturn($simpleLinkAttribute);

        $recordInformation = new RecordInformation('designer', 'tony_stark', ['en_US' => 'Tony Stark']);
        $getRecordInformationQuery->execute('designer', 'tony_stark')
            ->willReturn($recordInformation);

        $userContext->getCurrentLocaleCode()->willReturn('en_US');

        $this->getValue($record)->shouldReturn('Tony Stark');
    }

    function it_formats_a_multiple_reference_entity_links_value_without_labels(
        ResultRecordInterface $record,
        UserContext $userContext,
        GetRecordInformationQueryInterface $getRecordInformationQuery,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $value = ['data' => ['tony_stark', 'banner'], 'attribute' => 'designer_links'];

        $record->getValue('[values][foo]')->willReturn([$value]);

        $multipleLinksAttribute = new Attribute();
        $multipleLinksAttribute->setType(ReferenceEntityType::REFERENCE_ENTITY)
            ->setBackendType(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTIONS)
            ->setReferenceDataName('designer');
        $attributeRepository->findOneByIdentifier('designer_links')->willReturn($multipleLinksAttribute);

        $starkInformation = new RecordInformation('designer', 'tony_stark', []);
        $getRecordInformationQuery->execute('designer', 'tony_stark')
            ->willReturn($starkInformation);
        $bannerInformation = new RecordInformation('designer', 'banner', []);
        $getRecordInformationQuery->execute('designer', 'banner')
            ->willReturn($bannerInformation);

        $userContext->getCurrentLocaleCode()->willReturn('en_US');

        $this->getValue($record)->shouldReturn('[tony_stark], [banner]');
    }

    function it_formats_a_multiple_reference_entity_links_value_with_labels(
        ResultRecordInterface $record,
        UserContext $userContext,
        GetRecordInformationQueryInterface $getRecordInformationQuery,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $value = ['data' => ['tony_stark', 'banner'], 'attribute' => 'designer_links'];

        $record->getValue('[values][foo]')->willReturn([$value]);

        $multipleLinksAttribute = new Attribute();
        $multipleLinksAttribute->setType(ReferenceEntityType::REFERENCE_ENTITY)
            ->setBackendType(AttributeTypes::BACKEND_TYPE_REF_DATA_OPTIONS)
            ->setReferenceDataName('designer');
        $attributeRepository->findOneByIdentifier('designer_links')->willReturn($multipleLinksAttribute);

        $starkInformation = new RecordInformation('designer', 'tony_stark', ['en_US' => 'Tony Stark']);
        $getRecordInformationQuery->execute('designer', 'tony_stark')
            ->willReturn($starkInformation);
        $bannerInformation = new RecordInformation('designer', 'banner', ['en_US' => 'Banner the crazy']);
        $getRecordInformationQuery->execute('designer', 'banner')
            ->willReturn($bannerInformation);

        $userContext->getCurrentLocaleCode()->willReturn('en_US');

        $this->getValue($record)->shouldReturn('Tony Stark, Banner the crazy');
    }

    function it_formats_array_values(ResultRecordInterface $record)
    {
        $value = ['data' => ['Tony Stark', 'Bruce Banner']];
        $expectedFormattedValue = 'Tony Stark, Bruce Banner';

        $record->getValue('[values][foo]')->willReturn([$value]);

        $this->getValue($record)->shouldReturn($expectedFormattedValue);
    }
}

class PropertyConfigurationFake extends PropertyConfiguration
{
    public function __construct(array $params)
    {
        $this->params = $params;
    }
}
