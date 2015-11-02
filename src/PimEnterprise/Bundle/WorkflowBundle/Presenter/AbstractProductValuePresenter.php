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
    public function supports($data)
    {
        if ($data instanceof ProductValueInterface) {
            return $this->supportsChange($data->getAttribute()->getAttributeType());
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function presentOriginal($value, array $change)
    {
        return $this->renderer->renderOriginalDiff(
            $this->normalizeData($value->getData()),
            $this->normalizeChange($change)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function presentNew($value, array $change)
    {
        return $this->renderer->renderNewDiff(
            $this->normalizeData($value->getData()),
            $this->normalizeChange($change)
        );
    }

    /**
     * Whether or not this class can present the provided change
     *
     * @param string $attributeType
     *
     * @return bool
     */
    abstract protected function supportsChange($attributeType);

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
