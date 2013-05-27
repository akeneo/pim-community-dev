<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model;

/**
 * Flexible value interface, allow to define a flexible value without extends abstract class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
interface FlexibleValueInterface
{

    /**
     * Get attribute
     *
     * @return AbstractAttribute
     */
    public function getAttribute();

    /**
     * Get data
     *
     * @return mixed
     */
    public function getData();
}
