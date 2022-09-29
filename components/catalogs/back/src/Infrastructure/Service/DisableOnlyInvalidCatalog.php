<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Service;

use Akeneo\Catalogs\Application\Persistence\Catalog\DisableCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\FindOneCatalogByIdQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogProductSelectionCriteriaQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogProductValueFiltersQueryInterface;
use Akeneo\Catalogs\Application\Service\DisableOnlyInvalidCatalogInterface;
use Akeneo\Catalogs\Infrastructure\Validation\CatalogUpdatePayload;
use Akeneo\Catalogs\ServiceAPI\Events\InvalidCatalogDisabledEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DisableOnlyInvalidCatalog implements DisableOnlyInvalidCatalogInterface
{
    public function __construct(
        private FindOneCatalogByIdQueryInterface $findOneCatalogByIdQuery,
        private ValidatorInterface $validator,
        private GetCatalogProductSelectionCriteriaQueryInterface $getProductSelectionCriteriaQuery,
        private GetCatalogProductValueFiltersQueryInterface $getProductValueFiltersQuery,
        private DisableCatalogQueryInterface $disableCatalogQuery,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function disable(string $catalogId): bool
    {
        $catalog = $this->findOneCatalogByIdQuery->execute($catalogId);
        $violations = $this->validator->validate(
            [
                'enabled' => $catalog->isEnabled(),
                'product_selection_criteria' => $this->getProductSelectionCriteriaQuery->execute($catalog->getId()),
                'product_value_filters' => $this->getProductValueFiltersQuery->execute($catalog->getId()),
            ],
            [
                new CatalogUpdatePayload(),
            ]
        );

        if ($violations->count() > 0) {
            $this->disableCatalogQuery->execute($catalogId);

            $this->dispatcher->dispatch(new InvalidCatalogDisabledEvent($catalogId));

            return true;
        }

        return false;
    }
}
