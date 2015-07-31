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

use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Repository\AttributeOptionRepositoryInterface;

/**
 * Present changes on option data
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class OptionPresenter extends AbstractProductValuePresenter
{
    /** @var AttributeOptionRepositoryInterface */
    protected $repository;

    /**
     * @param AttributeOptionRepositoryInterface $repository
     */
    public function __construct(AttributeOptionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsChange($attributeType)
    {
        return AttributeTypes::OPTION_SIMPLE_SELECT === $attributeType;
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
        if (null === $change['data']) {
            return null;
        }

        return (string) $this->repository->findOneBy(['code' => $change['data']]);
    }
}
