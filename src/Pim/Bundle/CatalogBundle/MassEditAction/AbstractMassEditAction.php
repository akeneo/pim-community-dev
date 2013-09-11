<?php

namespace Pim\Bundle\CatalogBundle\MassEditAction;

/**
 * Class that Batch operations might extends for convenience purpose
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractMassEditAction implements MassEditAction
{
    /**
     * {@inheritdoc}
     */
    public function getFormOptions()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $products)
    {
    }

    /**
     * Get a parameter
     *
     * @param string $key
     * @param array  $parameters
     *
     * @throw InvalidArgumentException
     *
     * @return mixed
     */
    protected function getParameter($key, array $parameters, $default = null)
    {
        if (!array_key_exists($key, $parameters)) {
            return $default;
        }

        return $parameters[$key];
    }
}
