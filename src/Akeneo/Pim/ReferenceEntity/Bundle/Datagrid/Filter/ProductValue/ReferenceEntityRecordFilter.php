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

namespace Akeneo\Pim\ReferenceEntity\Bundle\Datagrid\Filter\ProductValue;

use Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
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
        ReferenceEntityRepositoryInterface $referenceEntityRepository
    ) {
        parent::__construct($factory, $util, $userContext, null, $attributeRepository);

        $this->referenceEntityRepository = $referenceEntityRepository;
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
}
