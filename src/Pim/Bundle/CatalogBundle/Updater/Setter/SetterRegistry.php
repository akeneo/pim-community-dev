<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Registry of setters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetterRegistry implements SetterRegistryInterface
{
    /** @var SetterInterface[] */
    protected $setters = [];

    /**
     * {@inheritdoc}
     */
    public function register(SetterInterface $setter)
    {
        $this->setters[] = $setter;
    }

    /**
     * {@inheritdoc}
     */
    public function get(AttributeInterface $attribute)
    {
        foreach ($this->setters as $setter) {
            if ($setter->supports($attribute)) {
                return $setter;
            }
        }

        throw new \LogicException(sprintf('Attribute "%s" is not supported by any setter', $attribute->getCode()));
    }
}
