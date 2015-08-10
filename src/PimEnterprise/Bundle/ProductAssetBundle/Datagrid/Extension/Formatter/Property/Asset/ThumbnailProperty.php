<?php

namespace PimEnterprise\Bundle\ProductAssetBundle\Datagrid\Extension\Formatter\Property\Asset;

use Pim\Bundle\DataGridBundle\Extension\Formatter\Property\ProductValue\TwigProperty;

/**
 * Thumbnail property for an asset
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class ThumbnailProperty extends TwigProperty
{
    /**
     * {@inheritdoc}
     *
     * Here we'll receive all references for the asset. We need to take the first reference who have a valid file.
     * In case no reference or no valid file is found, we give a path that will be interpreted as the default image
     * by the MediaController.
     */
    protected function convertValue($value)
    {
        foreach ($value as $reference) {
            if (null !== $file = $reference->getFile()) {
                return $this->getTemplate()->render(['path' => $file->getKey()]);
            }
        }

        return $this->getTemplate()->render(['path' => 'default']);
    }
}
