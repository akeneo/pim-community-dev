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

use Pim\Bundle\CatalogBundle\Repository\AttributeOptionRepositoryInterface;

/**
 * Present changes on options data
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class OptionsPresenter extends AbstractProductValuePresenter
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
    public function supportsChange(array $change)
    {
        return array_key_exists('options', $change);
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
        $options = $this->repository->findBy(['id' => explode(',', $change['options'])]);

        $result = [];
        foreach ($options as $option) {
            $result[] = (string) $option;
        }

        return $result;
    }
}
