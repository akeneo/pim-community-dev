<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Abstract setter
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractValueSetter implements SetterInterface
{
    /** @var array */
    protected $supportedTypes = [];

    /**
     * {@inheritdoc}
     */
    public function supports(AttributeInterface $attribute)
    {
        return in_array($attribute->getAttributeType(), $this->supportedTypes);
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedTypes()
    {
        return $this->supportedTypes;
    }
}
