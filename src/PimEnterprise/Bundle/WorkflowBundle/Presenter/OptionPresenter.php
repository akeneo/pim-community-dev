<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;

/**
 * Present changes on option data
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class OptionPresenter extends AbstractProductValuePresenter
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
        return array_key_exists('option', $change);
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
        return (string) $this->repository->find($change['option']);
    }
}
