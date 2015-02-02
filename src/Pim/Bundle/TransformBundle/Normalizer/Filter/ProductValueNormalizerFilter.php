<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Filter;

use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Filter for ProductValue objects.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNormalizerFilter implements NormalizerFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filter(Collection $objects, array $context = [])
    {
        $locales  = isset($context['locales']) ? $context['locales'] : [];
        $channels = isset($context['channels']) ? $context['channels'] : [];

        return $objects->filter(
            function ($value) use ($locales, $channels) {

                if (!$value instanceof ProductValueInterface) {
                    throw new \Exception('This filter only handles objects of type "ProductValueInterface"');
                }

                $attribute = $value->getAttribute();
                if (!empty($locales) && $attribute->isLocalizable() && !in_array($value->getLocale(), $locales)) {
                    return false;
                }

                if (!empty($channels) && $attribute->isScopable() && !in_array($value->getScope(), $channels)) {
                    return false;
                }

                return true;
            }
        );
    }
}
