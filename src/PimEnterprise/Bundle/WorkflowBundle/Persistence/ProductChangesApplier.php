<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Persistence;

use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Applies product changes
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductChangesApplier
{
    protected $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function apply(AbstractProduct $product, array $changes)
    {
        $this
            ->formFactory
            ->createBuilder('form', $product)
            ->add(
                'values',
                'pim_enrich_localized_collection',
                [
                    'type'               => 'pim_product_value',
                    'allow_add'          => false,
                    'allow_delete'       => false,
                    'by_reference'       => false,
                    'cascade_validation' => true,
                    'currentLocale'      => null,
                    'comparisonLocale'   => null,
                ]
            )
            ->getForm()
            ->submit($changes);
    }
}
