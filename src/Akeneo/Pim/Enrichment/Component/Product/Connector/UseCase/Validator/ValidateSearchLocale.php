<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ValidateSearchLocale
{
    private const COMPLETENESS_PROPERTY = 'completeness';

    /** @var IdentifiableObjectRepositoryInterface */
    private $localeRepository;

    public function __construct(
        IdentifiableObjectRepositoryInterface $localeRepository
    ) {
        $this->localeRepository = $localeRepository;
    }

    /**
     * @throws InvalidQueryException
     */
    public function validate(array $search, ?string $searchLocaleCode): void
    {
        $this->validateFilterStructureForLocaleKey($search);
        $localeCodes = $this->getLocaleCodesFromFilters($search);

        if (null !== $searchLocaleCode) {
            $localeCodes[] = $searchLocaleCode;
        }

        $localeCodes = array_unique($localeCodes);
        $errors = [];
        foreach ($localeCodes as $localeCode) {
            $locale = $this->localeRepository->findOneByIdentifier($localeCode);
            if (null === $locale || !$locale->isActivated()) {
                $errors[] = $localeCode;
            }
        }

        if (!empty($errors)) {
            $plural = count($errors) > 1 ?
                'Locales "%s" do not exist or are not activated.' : 'Locale "%s" does not exist or is not activated.';
            throw new InvalidQueryException(sprintf($plural, implode(', ', $errors)));
        }
    }

    private function validateFilterStructureForLocaleKey(array $search): void
    {
        foreach ($search as $propertyCode => $filters) {
            foreach ($filters as $filter) {
                if ($propertyCode === self::COMPLETENESS_PROPERTY) {
                    // locale is only supported officially for product model completeness filter.
                    // This part of the code add the support for product  completeness filter also,
                    // but it is actually not officially supported as it not in the API documentation.
                    // Removing the support of locale for the completeness filter for the products
                    // would not be considered as a BC break.
                    if (isset($filter['locales'])) {
                        if (!is_array($filter['locales'])) {
                            throw new InvalidQueryException(
                                sprintf('Property "completeness" expects an array with the key "locales".')
                            );
                        }
                    }
                } else {
                    if (isset($filter['locale']) && !is_string($filter['locale'])) {
                        throw new InvalidQueryException(
                            sprintf('Property "%s" expects a string with the key "locale".', $propertyCode)
                        );
                    }

                    if (isset($filter['locales'])) {
                        throw new InvalidQueryException(
                            sprintf('Property "%s" expects an array with the key "locale".', $propertyCode)
                        );
                    }
                }
            }
        }
    }

    private function getLocaleCodesFromFilters(array $search): array
    {
        $localeCodes = [];
        foreach ($search as $propertyCode => $filters) {
            foreach ($filters as $filter) {
                if (isset($filter['locale'])) {
                    $localeCodes[] = $filter['locale'];
                }

                if (isset($filter['locales'])) {
                    $localeCodes = array_merge($localeCodes, $filter['locales']);
                }
            }
        }

        return $localeCodes;
    }
}
