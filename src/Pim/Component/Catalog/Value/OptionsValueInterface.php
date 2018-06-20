<?php

namespace Pim\Component\Catalog\Value;

use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

/**
 * Interface for options product value
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface OptionsValueInterface extends ValueInterface
{
    /**
     * @return AttributeOptionInterface[]
     */
    public function getData();

    /**
     * @param string $code
     *
     * @return bool
     */
    public function hasCode($code);

    /**
     * @return array
     */
    public function getOptionCodes();
}
