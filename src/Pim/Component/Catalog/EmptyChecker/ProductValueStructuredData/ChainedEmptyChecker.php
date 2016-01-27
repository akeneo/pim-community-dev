<?php

namespace Pim\Component\Catalog\EmptyChecker\ProductValueStructuredData;

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
    public function isEmpty($attributeCode, $valueData)
    {
        foreach ($this->checkers as $checker) {
            if ($checker->supports($attributeCode)) {
                if ($checker->isEmpty($attributeCode, $valueData)) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        throw new \LogicException(
            sprintf(
                'No compatible EmptyCheckerInterface found for attribute "%s".',
                $attributeCode
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeCode)
    {
        foreach ($this->checkers as $checker) {
            if ($checker->supports($attributeCode)) {
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
