<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\ActionApplier;

use Akeneo\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Component\StorageUtils\Updater\PropertyAdderInterface;
use PimEnterprise\Component\CatalogRule\Model\ProductAddActionInterface;

/**
 * Adder action applier
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class AdderActionApplier implements ActionApplierInterface
{
    /** @var PropertyAdderInterface */
    protected $propertyAdder;

    /**
     * @param PropertyAdderInterface $propertyAdder
     */
    public function __construct(PropertyAdderInterface $propertyAdder)
    {
        $this->propertyAdder = $propertyAdder;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(ActionInterface $action, array $products = [])
    {
        foreach ($products as $product) {
            $this->propertyAdder->addData(
                $product,
                $action->getField(),
                $action->getItems(),
                $action->getOptions()
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ActionInterface $action)
    {
        return $action instanceof ProductAddActionInterface;
    }
}
