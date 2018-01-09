<?php

declare(strict_types=1);

namespace Pim\Bundle\DataGridBundle\Entity;

use Pim\Component\Catalog\Model\EntityWithValuesInterface;

/**
 * This interface aims to represent entities containing values that are filtered.
 * Entity is not loaded completely and should be use in read-only.
 *
 * The main reason about not loading every values is to improve hydration performance.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EntityWithFilteredValuesInterface extends EntityWithValuesInterface
{
}
