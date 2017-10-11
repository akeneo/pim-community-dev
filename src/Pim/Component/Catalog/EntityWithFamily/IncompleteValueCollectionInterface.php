<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\EntityWithFamily;

use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\ValueInterface;

/**
 * A collection of incomplete values depending on an entity with a family
 * {@see Pim\Component\Catalog\Model\EntityWithFamilyInterface}.
 *
 * An incomplete value collection holds all required values ({@see Pim\Component\Catalog\RequiredValue}) that are
 * either missing or empty in an entity.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface IncompleteValueCollectionInterface extends \Countable, \IteratorAggregate
{
    /**
     * Is there already a value with the same attribute, channel and locale than $value?
     *
     * @return bool
     */
    public function hasSame(ValueInterface $value): bool;

    /**
     * Get the list of attributes within those incomplete values
     *
     * @return Collection
     */
    public function attributes(): Collection;
}
