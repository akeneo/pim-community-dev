<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\EntityWithFamilyVariant;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\RemoveParentInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GrantedRemoveParent implements RemoveParentInterface
{
    /** @var RemoveParentInterface */
    private $removeParent;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(
        RemoveParentInterface $removeParent,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->removeParent = $removeParent;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function from(ProductInterface $product): void
    {
        if (!$this->authorizationChecker->isGranted(Attributes::OWN, $product)) {
            throw new ResourceAccessDeniedException(
                $product,
                'pim_enrich.mass_edit_action.convert_to_simple_product.message.error'
            );
        }

        $this->removeParent->from($product);
    }
}
