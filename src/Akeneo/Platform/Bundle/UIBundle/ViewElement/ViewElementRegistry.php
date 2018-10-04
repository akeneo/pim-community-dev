<?php

namespace Akeneo\Platform\Bundle\UIBundle\ViewElement;

/**
 * Registry of view elements
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ViewElementRegistry
{
    /** @var ViewElementInterface[] */
    protected $elements = [];

    /**
     * Register a view element
     *
     * @param ViewElementInterface $element
     * @param string               $type
     * @param int                  $position
     */
    public function add(ViewElementInterface $element, $type, $position)
    {
        if (!isset($this->elements[$type][$position])) {
            $this->elements[$type][$position] = $element;
        } else {
            $this->add($element, $type, ++$position);
        }
    }

    /**
     * Get the view elements for the given type
     *
     * @param string $type
     *
     * @return ViewElementInterface[]
     */
    public function get($type)
    {
        $elements = isset($this->elements[$type]) ? $this->elements[$type] : [];
        ksort($elements);

        return $elements;
    }
}
