<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ReferenceDataBundle\Workflow\Presenter;

use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\ReferenceDataBundle\Doctrine\ReferenceDataRepositoryResolver;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\AbstractProductValuePresenter;

/**
 * Abstract Present changes of reference data
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
abstract class AbstractReferenceDataPresenter extends AbstractProductValuePresenter
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ReferenceDataRepositoryResolver */
    protected $repositoryResolver;

    /** @var string */
    protected $referenceDataName;

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
    public function supports($data)
    {
        $supports = parent::supports($data);
        if ($supports) {
            $this->referenceDataName = $data->getAttribute()->getReferenceDataName();

            return true;
        }

        return false;
    }
}
