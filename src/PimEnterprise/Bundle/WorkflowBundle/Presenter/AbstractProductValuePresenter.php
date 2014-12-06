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

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * A product value diff presenter
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
abstract class AbstractProductValuePresenter implements PresenterInterface, RendererAwareInterface
{
    use RendererAware;

    /**
     * {@inheritdoc}
     */
    public function supports($data, array $change)
    {
        return $data instanceof ProductValueInterface && $this->supportsChange($change);
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
