<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Family;

use Akeneo\Catalogs\Application\Persistence\Family\GetFamilyLabelByCodeAndLocaleQueryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetFamilyLabelByCodeAndLocaleQuery implements GetFamilyLabelByCodeAndLocaleQueryInterface
{
    public function __construct(
        private readonly SearchableRepositoryInterface $searchableFamilyRepository,
    ) {
    }

    public function execute(string $code, string $locale): string
    {
        $families = $this->searchableFamilyRepository->findBySearch(null, ['identifiers' => [$code]]);

        $defaultValue = \sprintf('[%s]', $code);

        if ([] === $families) {
            return $defaultValue;
        }

        return $families[0]->setLocale($locale)->getLabel() ?: $defaultValue;
    }
}
