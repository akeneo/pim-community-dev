<?php

namespace Pim\Component\Catalog\EmptyChecker\ProductValue;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Chained empty checker
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChainedEmptyChecker implements EmptyCheckerInterface
{
    /** @var EmptyCheckerInterface[] */
    protected $checkers = [];

    /**
     * {@inheritdoc}
     */
    public function isEmpty(ProductValueInterface $productValue)
    {
        foreach ($this->checkers as $checker) {
            if ($checker->supports($productValue)) {
                if ($checker->isEmpty($productValue)) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        throw new \LogicException(
            sprintf(
                'No compatible EmptyCheckerInterface found for attribute type "%s".',
                $productValue->getAttribute()->getAttributeType()
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ProductValueInterface $productValue)
    {
        foreach ($this->checkers as $checker) {
            if ($checker->supports($productValue)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param EmptyCheckerInterface $checker
     *
     * @return EmptyCheckerInterface
     */
    public function addEmptyChecker(EmptyCheckerInterface $checker)
    {
        $this->checkers[] = $checker;

        return $this;
    }
}
