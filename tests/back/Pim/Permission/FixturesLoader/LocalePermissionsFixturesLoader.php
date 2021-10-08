<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\FixturesLoader;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Permission\Bundle\Manager\LocaleAccessManager;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Doctrine\Persistence\ObjectManager;

class LocalePermissionsFixturesLoader
{
    private LocaleAccessManager $localeAccessManager;
    private ObjectManager $objectManager;
    private LocaleRepositoryInterface $localeRepository;

    public function __construct(
        LocaleRepositoryInterface $localeRepository,
        LocaleAccessManager $localeAccessManager,
        ObjectManager $objectManager
    ) {
        $this->localeRepository = $localeRepository;
        $this->localeAccessManager = $localeAccessManager;
        $this->objectManager = $objectManager;
    }

    /**
     * @param string[] $localeCodes
     */
    public function givenTheRightOnLocaleCodes(string $accessLevel, GroupInterface $userGroup, array $localeCodes): void
    {
        foreach ($localeCodes as $localeCode) {
            $locale = $this->localeRepository->findOneByIdentifier($localeCode);

            $this->localeAccessManager->revokeAccess($locale);
            $this->objectManager->flush($locale);

            $this->localeAccessManager->grantAccess($locale, $userGroup, $accessLevel);
        }
    }
}
