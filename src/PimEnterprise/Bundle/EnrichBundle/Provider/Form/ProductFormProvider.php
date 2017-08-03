<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Provider\Form;

use Pim\Bundle\EnrichBundle\Provider\Form\FormProviderInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Form provider for product
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ProductFormProvider implements FormProviderInterface
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function getForm($product): string
    {
        return $this->authorizationChecker->isGranted(Attributes::EDIT, $product) ?
            'pim-product-edit-form' :
            'pimee-product-view-form';
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element): bool
    {
        return $element instanceof ProductInterface;
    }
}
