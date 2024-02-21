<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Collection of common attributes, they don't belong to any variant attribute set.
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CommonAttributeCollection extends ArrayCollection
{
    /**
     * @param Collection $collection
     *
     * @return CommonAttributeCollection
     */
    public static function fromCollection(Collection $collection): CommonAttributeCollection
    {
        return new static($collection->toArray());
    }
}
