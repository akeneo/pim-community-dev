<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Proposition;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Comparator\ComparatorInterface;

/**
 * Product change set computer during proposition workflow
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ChangeSetComputer implements ChangeSetComputerInterface
{
    /** @var ComparatorInterface $comparator */
    protected $comparator;

    public function __construct(ComparatorInterface $comparator)
    {
        $this->comparator = $comparator;
    }

    public function compute(ProductInterface $product, array $submittedData)
    {
        $changeSet = [];
        if (!isset($submittedData['values'])) {
            return $changeSet;
        }

        $currentValues = $product->getValues();
        foreach ($submittedData['values'] as $key => $data) {
            if ($currentValues->containsKey($key)) {
                $value = $currentValues->get($key);

                if (null !== $changes = $this->comparator->getChanges($value, $data)) {
                    $changes = array_merge(
                        $changes,
                        [
                            '__context__' => [
                                'attribute' => $value->getAttribute()->getCode(),
                                    'locale' => $value->getLocale(),
                                    'scope' => $value->getScope(),
                                ]
                            ]
                        );
                }

                $changeSet['values'][$key] = $changes;
            }
        }

        return $changeSet;
    }
}
