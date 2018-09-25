<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Form\Provider;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
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
