<?php

namespace PimEnterprise\Bundle\EnrichBundle\Form\View;

use Symfony\Component\Form\FormView;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\EnrichBundle\Form\View\ProductFormView as PimProductFormView;

use PimEnterprise\Bundle\SecurityBundle\Voter\AttributeGroupVoter;

/**
 * Extending product form view adding rights
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductFormView extends PimProductFormView
{
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * Construct
     *
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareAttributeView(AbstractAttribute $attribute, ProductValueInterface $value, FormView $view)
    {
        $attributeView = parent::prepareAttributeView($attribute, $value, $view);

        $attributeView['allowValueCreation'] = $attributeView['allowValueCreation']
            && $this->securityContext->isGranted(AttributeGroupVoter::EDIT_ATTRIBUTES, $attribute->getVirtualGroup());

        return $attributeView;
    }
}
