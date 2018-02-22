<?php

declare(strict_types=1);

namespace PimEnterprise\Component\Security\Authorization;

use Akeneo\Component\Classification\CategoryAwareInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Checks if the $categoryAwareEntity is granted against the current authentication token.
 * If not granted it denies the entity by throwing an exception.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class DenyNotGrantedCategorizedEntity
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
     * Checks if the $categoryAwareEntity is granted against the current authentication token.
     * If not granted it denies the entity by throwing an exception.
     *
     * @param CategoryAwareInterface $categoryAwareEntity
     *
     * @throws ResourceAccessDeniedException
     */
    public function denyIfNotGranted(CategoryAwareInterface $categoryAwareEntity): void
    {
        if (!$this->authorizationChecker->isGranted(Attributes::VIEW, $categoryAwareEntity)) {
            if ($categoryAwareEntity instanceof ProductModelInterface) {
                throw new ResourceAccessDeniedException(
                    $categoryAwareEntity,
                    sprintf(
                        'You can neither view, nor update, nor delete the product model "%s", as it is only ' .
                        'categorized in categories on which you do not have a view permission.',
                        $categoryAwareEntity->getCode()
                    )
                );
            }

            if ($categoryAwareEntity instanceof ProductInterface) {
                throw new ResourceAccessDeniedException(
                    $categoryAwareEntity,
                    sprintf(
                        'You can neither view, nor update, nor delete the product "%s", as it is only categorized ' .
                        'in categories on which you do not have a view permission.',
                        $categoryAwareEntity->getIdentifier()
                    )
                );
            }

            throw new ResourceAccessDeniedException(
                $categoryAwareEntity,
                'You can neither view, nor update, nor delete this entity, as it is only categorized in categories ' .
                'on which you do not have a view permission.'
            );
        }
    }
}
