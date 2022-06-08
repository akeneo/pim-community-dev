<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Security;

use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
trait DenyAccessUnlessGrantedTrait
{
    private SecurityFacadeInterface $security;

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
}
