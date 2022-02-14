<?php

declare(strict_types=1);

namespace Akeneo\Channel\Test\Acceptance\InMemory;

use Akeneo\Channel\API\Query\GetEditableLocaleCodes;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InMemoryGetEditableLocaleCodes implements GetEditableLocaleCodes
{
    public function __construct(private LocaleRepositoryInterface $localeRepository)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function forUserId(int $userId): array
    {
        $localeCodes = [];
        /** @var LocaleInterface $locale */
        foreach ($this->localeRepository->findAll() as $locale) {
            if ($locale->isActivated()) {
                $localeCodes[] = $locale->getCode();
            }
        }

        return $localeCodes;
    }
}
