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
use Pim\Component\Catalog\AttributeTypes;

/**
 * Present changes on option data
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class OptionPresenter extends AbstractProductValuePresenter
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

        $identifier = sprintf('%s.%s', $change['attribute'], $change['data']);

        return (string) $this->repository->findOneByIdentifier($identifier);
    }
}
