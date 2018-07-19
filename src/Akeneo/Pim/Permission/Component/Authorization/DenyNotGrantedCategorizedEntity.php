<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Component\Authorization;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Exception\ResourceViewAccessDeniedException;
use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
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
     * If the view permission is not granted, the message will be voluntary vague.
     *
     * @param CategoryAwareInterface $categoryAwareEntity
     *
     * @throws ResourceViewAccessDeniedException
     */
    public function denyIfNotGranted(CategoryAwareInterface $categoryAwareEntity): void
    {
        if (!$this->authorizationChecker->isGranted(Attributes::VIEW, $categoryAwareEntity)) {
            if ($categoryAwareEntity instanceof ProductModelInterface) {
                throw new ResourceViewAccessDeniedException(
                    $categoryAwareEntity,
                    sprintf('Product model "%s" does not exist.', $categoryAwareEntity->getCode())
                );
            }

            if ($categoryAwareEntity instanceof ProductInterface) {
                throw new ResourceViewAccessDeniedException(
                    $categoryAwareEntity,
                    sprintf('Product "%s" does not exist.', $categoryAwareEntity->getIdentifier())
                );
            }

            throw new ResourceViewAccessDeniedException($categoryAwareEntity, 'This entity does not exist.');
        }
    }
}
