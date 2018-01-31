<?php

namespace Pim\Bundle\CatalogBundle\Builder;

use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Enrich\Model\ChosableInterface;

/**
 * ChoicesBuilderInterface implementation
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChoicesBuilder implements ChoicesBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildChoices($items)
    {
        $choices = [];
        foreach ($items as $item) {
            if (!$item instanceof ChosableInterface) {
                throw new \InvalidArgumentException(sprintf(
                    '%s must implement Pim\Component\Enrich\Model\ChosableInterface',
                    ClassUtils::getClass($item)
                ));
            }

            $choices[$item->getChoiceLabel()] = $item->getChoiceValue();
        }

        return $choices;
    }
}
