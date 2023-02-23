<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Security;

use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
trait DenyAccessUnlessGrantedTrait
{
    private SecurityFacadeInterface $security;

    private function denyAccessUnlessOwnerOfCatalog(Catalog $catalog, string $username): void
    {
        if ($catalog->getOwnerUsername() !== $username) {
            throw new NotFoundHttpException(
                \sprintf('Catalog "%s" does not exist or you can\'t access it.', $catalog->getId()),
            );
        }
    }

    private function denyAccessUnlessGrantedToListCatalogs(): void
    {
        if (!$this->security->isGranted('pim_api_catalog_list')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to access app catalogs.');
        }
    }

    private function denyAccessUnlessGrantedToEditCatalogs(): void
    {
        if (!$this->security->isGranted('pim_api_catalog_edit')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to edit app catalogs.');
        }
    }

    private function denyAccessUnlessGrantedToRemoveCatalogs(): void
    {
        if (!$this->security->isGranted('pim_api_catalog_remove')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to delete app catalogs.');
        }
    }

    private function denyAccessUnlessGrantedToListProducts(): void
    {
        if (!$this->security->isGranted('pim_api_product_list')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to access products.');
        }
    }
}
