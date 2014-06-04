<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

/**
 * A product value diff presenter
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractProductValuePresenter implements PresenterInterface, RendererAwareInterface
{
    use RendererAware;

    /**
     * {@inheritdoc}
     */
    public function supports($data, array $change)
    {
        return $data instanceof AbstractProductValue && $this->supportsChange($change);
    }

    /**
     * {@inheritdoc}
     */
    public function present($value, array $change)
    {
        return $this->renderer->renderDiff(
            $this->normalizeData($value->getData()),
            $this->normalizeChange($change)
        );
    }

    /**
     * Wether or not this class can present the provided change
     *
     * @param array $change
     *
     * @return boolean
     */
    abstract protected function supportsChange(array $change);

    /**
     * Normalize data
     *
     * @param mixed $data
     *
     * @return array|string
     */
    protected function normalizeData($data)
    {
        return [];
    }

    /**
     * Normalize change
     *
     * @param array $change
     *
     * @return array|string
     */
    protected function normalizeChange(array $change)
    {
        return [];
    }
}
