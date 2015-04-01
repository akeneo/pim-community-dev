<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ReferenceDataBundle\Workflow\Presenter;

use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\AbstractProductValuePresenter;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Present changes on a collection of reference data
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataCollectionPresenter extends AbstractProductValuePresenter
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ConfigurationRegistryInterface */
    protected $registry;

    /** @var RegistryInterface */
    protected $doctrine;

    /**
     * @param AttributeRepositoryInterface   $attributeRepository
     * @param ConfigurationRegistryInterface $registry
     * @param RegistryInterface              $doctrine
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ConfigurationRegistryInterface $registry,
        RegistryInterface $doctrine
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->registry            = $registry;
        $this->doctrine            = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsChange(array $change)
    {
        $attribute = $this->getAttribute($change);

        return (null !== $attribute && 'pim_reference_data_multiselect' === $attribute->getAttributeType());
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        $result = [];
        foreach ($data as $reference) {
            $result[] = (string) $reference;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        $attribute = $this->getAttribute($change);
        if (null === $attribute) {
            return;
        }

        $result = [];
        $referenceDataName = $attribute->getReferenceDataName();
        $referenceDataClass = $this->registry->get($referenceDataName)->getClass();
        $references = $this->doctrine->getRepository($referenceDataClass)->findBy(['id' => explode(',', $change[$referenceDataName])]);

        foreach ($references as $reference) {
            $result[] = (string) $reference;
        }

        return $result;
    }

    /**
     * Get attribute
     *
     * @param array $change
     *
     * @return \Pim\Bundle\CatalogBundle\Model\AttributeInterface
     */
    protected function getAttribute(array $change = [])
    {
        $code = $change['__context__']['attribute'];

        return $this->attributeRepository->findOneBy(['code' => $code]);
    }
}
