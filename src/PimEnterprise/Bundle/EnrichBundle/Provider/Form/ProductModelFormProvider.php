<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Provider\Form;

use Pim\Bundle\EnrichBundle\Provider\Form\FormProviderInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Form provider for product model
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class ProductModelFormProvider implements FormProviderInterface
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

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
    public function getForm($productModel): string
    {
        return $this->authorizationChecker->isGranted(Attributes::EDIT, $productModel) ?
            'pim-product-model-edit-form' :
            'pimee-product-model-view-form';
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element): bool
    {
        return $element instanceof ProductModelInterface;
    }
}
