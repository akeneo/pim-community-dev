<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Family;

use Akeneo\Catalogs\Application\Persistence\Family\GetFamilyLabelByCodeAndLocaleQueryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetFamilyLabelByCodeAndLocaleQuery implements GetFamilyLabelByCodeAndLocaleQueryInterface
{
    /** @var array<string,array<string,string>> $familyLabelByCode */
    private array $familyLabelByCode = [];

    public function __construct(
        private readonly SearchableRepositoryInterface $searchableFamilyRepository,
    ) {
    }

    public function execute(string $code, string $locale): string
    {
        if (isset($this->familyLabelByCode[$code]) && isset($this->familyLabelByCode[$code][$locale])) {
            return $this->familyLabelByCode[$code][$locale];
        }

        $families = $this->searchableFamilyRepository->findBySearch(null, ['identifiers' => [$code]]);

        $label = \sprintf('[%s]', $code);

        if ([] !== $families) {
            /** @var FamilyInterface $family */
            $family = $families[0];
            $family->setLocale($locale);
            $label = $family->getLabel() ?: $label;
        }

        if (!isset($this->familyLabelByCode[$code])) {
            $this->familyLabelByCode[$code] = [];
        }
        $this->familyLabelByCode[$code][$locale] = $label;

        return $label;
    }
}
