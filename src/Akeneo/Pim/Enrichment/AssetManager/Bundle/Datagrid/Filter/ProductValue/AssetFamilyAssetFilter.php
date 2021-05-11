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

namespace Akeneo\Pim\Enrichment\AssetManager\Bundle\Datagrid\Filter\ProductValue;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindExistingAssetCodesInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use Oro\Bundle\PimFilterBundle\Filter\ProductValue\ChoiceFilter;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Filter on asset family assets in datagrid
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class AssetFamilyAssetFilter extends ChoiceFilter
{
    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var FindExistingAssetCodesInterface */
    private $existingAssetCodesQuery;

    /**
     * Constructor
     *
     * @param FormFactoryInterface           $factory
     * @param ProductFilterUtility           $util
     * @param UserContext                    $userContext
     * @param AttributeRepositoryInterface   $attributeRepository
     * @param ConfigurationRegistryInterface $registry
     */
    public function __construct(
        FormFactoryInterface $factory,
        ProductFilterUtility $util,
        UserContext $userContext,
        AttributeRepositoryInterface $attributeRepository,
        AttributeOptionRepositoryInterface $attributeOptionRepository,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        FindExistingAssetCodesInterface $existingAssetCodesQuery
    ) {
        parent::__construct($factory, $util, $userContext, $attributeRepository, $attributeOptionRepository);

        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->existingAssetCodesQuery = $existingAssetCodesQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        $operator = $this->getOperator($data['type']);

        $this->util->applyFilter(
            $ds,
            $this->get(ProductFilterUtility::DATA_NAME_KEY),
            $operator,
            $data['value']
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormOptions()
    {
        $attribute = $this->getAttribute();
        $referenceDataName = $attribute->getReferenceDataName();

        return array_merge(
            parent::getFormOptions(),
            [
                'choice_url' => 'akeneo_asset_manager_asset_index_rest',
                'choice_url_params' => [
                    'assetFamilyIdentifier' => $referenceDataName,
                ],
            ]
        );
    }

    public function getMetadata()
    {
        $metadata = parent::getMetadata();
        $metadata[FilterUtility::TYPE_KEY] = 'asset-family-collection';

        return $metadata;
    }

    /**
     * Filter options value to have only existing asset codes
     *
     * @param string[] $optionCodes
     *
     * @return string[]
     */
    protected function filterOnlyExistingOptions($optionCodes): array
    {
        $attribute = $this->getAttribute();
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($attribute->getReferenceDataName());

        return $this->existingAssetCodesQuery->find($assetFamilyIdentifier, $optionCodes);
    }
}
