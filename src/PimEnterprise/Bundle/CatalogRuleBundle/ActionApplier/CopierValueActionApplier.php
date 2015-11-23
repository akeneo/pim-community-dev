<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\ActionApplier;

use Akeneo\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Component\StorageUtils\Updater\PropertyCopierInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;

/**
 * Copier value action applier
 *
 * @author Julien Sanchez <julien@akeneo.com>
 * @deprecated will be removed in 1.6 please use
 *             PimEnterprise\Component\CatalogRule\ActionApplier\CopierActionApplier
 */
class CopierValueActionApplier implements ActionApplierInterface
{
    /** @var PropertyCopierInterface */
    protected $propertyCopier;

    /**
     * @param PropertyCopierInterface $propertyCopier
     */
    public function __construct(PropertyCopierInterface $propertyCopier)
    {
        $this->propertyCopier = $propertyCopier;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(ActionInterface $action, array $products = [])
    {
        foreach ($products as $product) {
            $this->propertyCopier->copyData(
                $product,
                $product,
                $action->getFromField(),
                $action->getToField(),
                [
                    'from_locale' => $action->getFromLocale(),
                    'from_scope'  => $action->getFromScope(),
                    'to_locale'   => $action->getToLocale(),
                    'to_scope'    => $action->getToScope()
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ActionInterface $action)
    {
        return $action instanceof ProductCopyValueActionInterface;
    }
}
