<?php

namespace Pim\Component\Catalog\Validator;

use Pim\Component\Catalog\Model\ProductInterface;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UniqueAxesCombinationSet
{
    /** @var array */
    protected $uniqueAxesCombination;

    /**
     * Initializes the set.
     */
    public function __construct()
    {
        $this->uniqueAxesCombination = [];
    }

    /**
     * Resets the set.
     */
    public function reset()
    {
        $this->uniqueAxesCombination = [];
    }

    /**
     * Returns TRUE if axes combination has been added, FALSE if it already
     * exists inside the set.
     *
     * @param ProductInterface $product
     * @param string           $axesCombination
     *
     * @return bool
     */
    public function addCombination(ProductInterface $product, $axesCombination)
    {
        $groupCode = $product->getVariantGroup()->getCode();
        $identifier = $product->getIdentifier();

        if (isset($this->uniqueAxesCombination[$groupCode][$axesCombination])) {
            $cachedIdentifier = $this->uniqueAxesCombination[$groupCode][$axesCombination];
            if ($cachedIdentifier !== $identifier) {
                return false;
            }
        }
        if (!isset($this->uniqueAxesCombination[$groupCode])) {
            $this->uniqueAxesCombination[$groupCode] = [];
        }

        if (!isset($this->uniqueAxesCombination[$groupCode][$axesCombination])) {
            $this->uniqueAxesCombination[$groupCode][$axesCombination] = $identifier;
        }

        return true;
    }
}
