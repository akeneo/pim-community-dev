<?php

namespace Pim\Component\Catalog\Completeness\Checker;

use Pim\Component\Catalog\Completeness\Checker\Attribute\AttributeCompleteCheckerInterface;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompleteCheckerRegistry implements CompleteCheckerRegistryInterface
{
    /** @var AttributeCompleteCheckerInterface[] */
    protected $attributeCheckers = [];

    /**
     * @return AttributeCompleteCheckerInterface[]
     */
    public function getAttributeCheckers()
    {
        return $this->attributeCheckers;
    }

    /**
     * @param AttributeCompleteCheckerInterface $attributeCompleteChecker
     */
    public function registerAttributeChecker(AttributeCompleteCheckerInterface $attributeCompleteChecker)
    {
        $this->attributeCheckers[] = $attributeCompleteChecker;
    }
}
