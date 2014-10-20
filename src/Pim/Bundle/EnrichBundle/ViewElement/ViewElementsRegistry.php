<?php

namespace Pim\Bundle\EnrichBundle\ViewElement;

/**
 * Registry of view elements
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ViewElementsRegistry
{
    /** @var array */
    protected $views;

    /**
     * Register available views
     *
     * @param array $views
     */
    public function setViews(array $views = [])
    {
        $this->views = $views;
    }

    /**
     * Get the views for the given type and identifier
     *
     * @param string $type
     * @param string $identifier
     *
     * @return ViewElementInterface[]
     * @throws \InvalidArgumentException
     */
    public function getViews($type, $identifier)
    {
        if (!isset($this->views[$type])) {
            throw new \InvalidArgumentException(sprintf('There is no %s view registered', $type));
        }

        if (!isset($this->views[$type][$identifier])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The %s view for the identifier %s doesn\'t exists',
                    $type,
                    $identifier
                )
            );
        }

        return $this->views[$type][$identifier];
    }
}
