<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Locale;

use Akeneo\Catalogs\Application\Persistence\Locale\GetLocalesByCodeQueryInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetLocalesByCodeQuery implements GetLocalesByCodeQueryInterface
{
    public function __construct(private LocaleRepositoryInterface $localeRepository)
    {
    }

    /**
     * @inheritDoc
     */
    public function execute(array $codes, int $page = 1, int $limit = 20): array
    {
        $locales = [];

        /** @var array<LocaleInterface> $activatedLocales */
        $activatedLocales = $this->localeRepository->findBy(
            [
                'code' => $codes,
                'activated' => true,
            ],
            [],
            $limit,
            ($page - 1) * $limit,
        );

        foreach ($activatedLocales as $locale) {
            if (\in_array($locale->getCode(), $codes, true)) {
                $locales[] = [
                    'code' => $locale->getCode(),
                    'label' => $locale->getName() ?? \sprintf('[%s]', $locale->getCode()),
                ];
            }
        }

        return $locales;
    }
}
