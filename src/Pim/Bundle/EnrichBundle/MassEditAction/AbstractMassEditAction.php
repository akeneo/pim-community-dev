<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction;

/**
 * Class that Batch operations might extend for convenience purposes
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractMassEditAction implements MassEditActionInterface
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
     * @param mixed  $default
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
