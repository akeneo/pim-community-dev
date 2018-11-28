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

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\Datagrid\Filter\ProductValue;

use Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\SqlFindExistingRecordCodes;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use Oro\Bundle\PimFilterBundle\Filter\ProductValue\ChoiceFilter;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Filter on reference entity records in datagrid
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class ReferenceEntityRecordFilter extends ChoiceFilter
{
    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var SqlFindExistingRecordCodes */
    private $existingRecordCodesQuery;

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
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        SqlFindExistingRecordCodes $existingRecordCodesQuery
    ) {
        parent::__construct($factory, $util, $userContext, $attributeRepository, $attributeOptionRepository);

        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->existingRecordCodesQuery = $existingRecordCodesQuery;
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
                'choice_url' => 'akeneo_reference_entities_record_index_rest',
                'choice_url_params' => [
                    'referenceEntityIdentifier' => $referenceDataName,
                ],
            ]
        );
    }

    public function getMetadata()
    {
        $metadata = parent::getMetadata();
        $metadata[FilterUtility::TYPE_KEY] = 'reference-entity-collection';
        $metadata[FilterUtility::ENABLED_KEY] = false;

        return $metadata;
    }

    /**
     * Filter options value to have only existing record codes
     *
     * @param string[] $optionCodes
     *
     * @return string[]
     */
    protected function filterOnlyExistingOptions($optionCodes): array
    {
        $attribute = $this->getAttribute();
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($attribute->getReferenceDataName());

        return ($this->existingRecordCodesQuery)($referenceEntityIdentifier, $optionCodes);
    }
}
