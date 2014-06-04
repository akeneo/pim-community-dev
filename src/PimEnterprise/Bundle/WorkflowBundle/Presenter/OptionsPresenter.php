<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;

/**
 * Present changes on options data
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class OptionsPresenter extends AbstractProductValuePresenter
{
    /** @var AttributeOptionRepository */
    protected $repository;

    /**
     * @param AttributeOptionRepository $repository
     */
    public function __construct(AttributeOptionRepository $repository)
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
