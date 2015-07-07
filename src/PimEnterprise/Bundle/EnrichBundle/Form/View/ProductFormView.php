<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Form\View;

use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\EnrichBundle\Form\View\ProductFormView as BaseProductFormView;
use Pim\Bundle\EnrichBundle\Form\View\ViewUpdater\ViewUpdaterRegistry;
use Symfony\Component\Form\FormView;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Extending product form view adding permissions
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductFormView extends BaseProductFormView
{
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * Construct
     *
     * @param ViewUpdaterRegistry      $viewUpdaterRegistry
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(ViewUpdaterRegistry $viewUpdaterRegistry, SecurityContextInterface $securityContext)
    {
        parent::__construct($viewUpdaterRegistry);

        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareAttributeView(AttributeInterface $attribute, ProductValueInterface $value, FormView $view)
    {
        $attributeView = parent::prepareAttributeView($attribute, $value, $view);

        $attributeView['allowValueCreation'] = $attributeView['allowValueCreation']
            && $this->securityContext->isGranted(Attributes::EDIT_ATTRIBUTES, $attribute->getGroup());

        return $attributeView;
    }
}
