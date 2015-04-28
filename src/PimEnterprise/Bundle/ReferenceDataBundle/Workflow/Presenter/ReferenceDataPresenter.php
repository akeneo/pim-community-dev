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

use Pim\Bundle\ReferenceDataBundle\Doctrine\ReferenceDataRepositoryResolver;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\AbstractProductValuePresenter;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Present changes on reference data
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataPresenter extends AbstractProductValuePresenter
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ReferenceDataRepositoryResolver */
    protected $repositoryResolver;

    /**
     * @param AttributeRepositoryInterface    $attributeRepository
     * @param ReferenceDataRepositoryResolver $repositoryResolver
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ReferenceDataRepositoryResolver $repositoryResolver
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->repositoryResolver  = $repositoryResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsChange(array $change)
    {
        $attribute = $this->getAttribute($change);

        return (null !== $attribute && 'pim_reference_data_simpleselect' === $attribute->getAttributeType());
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        return (string) $data;
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

        $referenceDataName = $attribute->getReferenceDataName();
        $repository = $this->repositoryResolver->resolve($referenceDataName);

        return (string) $repository->find($change[$referenceDataName]);
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
        return $this->attributeRepository->findOneBy(['code' => key($change)]);
    }
}
