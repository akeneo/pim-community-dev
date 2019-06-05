<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\LocaleCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Query\SelectActiveLocaleCodesManagedByFranklinQueryInterface;
use Akeneo\Test\Acceptance\Locale\InMemoryLocaleRepository;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class InMemorySelectActiveLocaleCodesManagedByFranklinQuery implements SelectActiveLocaleCodesManagedByFranklinQueryInterface
{
    /**
     * @var InMemoryLocaleRepository
     */
    private $localeRepository;

    public function __construct(InMemoryLocaleRepository $localeRepository)
    {
        $this->localeRepository = $localeRepository;
    }

    /**
     * @return LocaleCode[]
     */
    public function execute(): array
    {
        $activatedLocaleCodes = $this->localeRepository->getActivatedLocaleCodes();

        $enActiveLocaleCodes = array_filter(
            $activatedLocaleCodes,
            function ($activatedLocaleCode) {
                return 'en_' === substr($activatedLocaleCode, 0, 3);
            }
        );

        return array_map(function ($row) {
            return new LocaleCode($row['code']);
        }, $enActiveLocaleCodes);
    }
}
