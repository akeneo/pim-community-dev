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

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Bundle\EnrichBundle\Form\View\ProductFormView as BaseProductFormView;
use Pim\Bundle\EnrichBundle\Form\View\ViewUpdater\ViewUpdaterRegistry;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Form\FormView;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Extending product form view adding permissions
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductFormView extends BaseProductFormView
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * Construct
     *
     * @param ViewUpdaterRegistry           $viewUpdaterRegistry
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        ViewUpdaterRegistry $viewUpdaterRegistry,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        parent::__construct($viewUpdaterRegistry);

        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareAttributeView(AttributeInterface $attribute, ProductValueInterface $value, FormView $view)
    {
        $attributeView = parent::prepareAttributeView($attribute, $value, $view);

        $attributeView['allowValueCreation'] = $attributeView['allowValueCreation']
            && $this->authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $attribute->getGroup());

        return $attributeView;
    }
}
