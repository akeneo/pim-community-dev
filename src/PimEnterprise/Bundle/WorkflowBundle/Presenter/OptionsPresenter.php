<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;

/**
 * Present changes on options data
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class OptionsPresenter extends AbstractProductValuePresenter
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $repository;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     */
    public function __construct(IdentifiableObjectRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsChange($attributeType)
    {
        return AttributeTypes::OPTION_MULTI_SELECT === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        $result = [];
        foreach ($data as $option) {
            $result[] = (string) $option;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        if (null === $change['data']) {
            return null;
        }

        $result = [];

        foreach ($change['data'] as $option) {
            $identifier = sprintf('%s.%s', $change['attribute'], $option);
            $result[] = (string) $this->repository->findOneByIdentifier($identifier);
        }

        return $result;
    }
}
