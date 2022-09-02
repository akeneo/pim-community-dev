<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\FixturesLoader;

use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Permission\Bundle\Manager\LocaleAccessManager;
use Akeneo\UserManagement\Component\Model\GroupInterface;

class LocalePermissionsFixturesLoader
{
    private LocaleAccessManager $localeAccessManager;
    private LocaleRepositoryInterface $localeRepository;

    public function __construct(
        LocaleRepositoryInterface $localeRepository,
        LocaleAccessManager $localeAccessManager
    ) {
        $this->localeRepository = $localeRepository;
        $this->localeAccessManager = $localeAccessManager;
    }

    /**
     * @param string[] $localeCodes
     */
    public function givenTheRightOnLocaleCodes(string $accessLevel, GroupInterface $userGroup, array $localeCodes): void
    {
        foreach ($localeCodes as $localeCode) {
            $locale = $this->localeRepository->findOneByIdentifier($localeCode);

            $this->localeAccessManager->revokeAccess($locale);
            $this->localeAccessManager->grantAccess($locale, $userGroup, $accessLevel);
        }
    }
}
