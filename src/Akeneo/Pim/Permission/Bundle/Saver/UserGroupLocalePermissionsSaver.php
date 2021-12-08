<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Saver;

use Akeneo\Pim\Permission\Bundle\Manager\LocaleAccessManager;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Locale\GetActiveLocaleReferenceFromCode;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Locale\GetActiveLocalesAccessesWithHighestLevel;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Locale\GetAllActiveLocalesCodes;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository;
use Akeneo\UserManagement\Component\Model\GroupInterface;

class UserGroupLocalePermissionsSaver
{
    private const DEFAULT_PERMISSION_EDIT = 'locale_edit';
    private const DEFAULT_PERMISSION_VIEW = 'locale_view';

    private const PERMISSION_EDIT = Attributes::EDIT_ITEMS;
    private const PERMISSION_VIEW = Attributes::VIEW_ITEMS;

    private LocaleAccessManager $localeAccessManager;
    private GroupRepository $groupRepository;
    private SaverInterface $groupSaver;
    private GetAllActiveLocalesCodes $getAllActiveLocalesCodes;
    private GetActiveLocalesAccessesWithHighestLevel $getActiveLocalesAccessesWithHighestLevel;
    private GetActiveLocaleReferenceFromCode $getActiveLocaleReferenceFromCode;

    public function __construct(
        LocaleAccessManager $localeAccessManager,
        GroupRepository $groupRepository,
        SaverInterface $groupSaver,
        GetAllActiveLocalesCodes $getAllActiveLocalesCodes,
        GetActiveLocalesAccessesWithHighestLevel $getActiveLocalesAccessesWithHighestLevel,
        GetActiveLocaleReferenceFromCode $getActiveLocaleReferenceFromCode
    ) {
        $this->groupRepository = $groupRepository;
        $this->groupSaver = $groupSaver;
        $this->localeAccessManager = $localeAccessManager;
        $this->getAllActiveLocalesCodes = $getAllActiveLocalesCodes;
        $this->getActiveLocalesAccessesWithHighestLevel = $getActiveLocalesAccessesWithHighestLevel;
        $this->getActiveLocaleReferenceFromCode = $getActiveLocaleReferenceFromCode;
    }

    /**
     * @param string $groupName
     * @param array{
     *      edit: array{
     *          all: bool,
     *          identifiers: string[],
     *      },
     *      view: array{
     *          all: bool,
     *          identifiers: string[],
     *      }
     * } $permissions
     *
     * @throws \LogicException
     */
    public function save(string $groupName, array $permissions): void
    {
        $group = $this->getUserGroup($groupName);
        $this->updateDefaultPermissions($group, $permissions);

        $affectedLocalesCodes = $this->getAffectedLocalesCodes($permissions);
        $highestAccessLevelIndexedByLocaleCode = $this->getHighestAccessLevelIndexedByLocaleCode($affectedLocalesCodes, $permissions);
        $existingHighestAccessLevelIndexedByLocaleCode = $this->getActiveLocalesAccessesWithHighestLevel->execute($group->getId());

        $removedLocaleCodes = array_diff(array_keys($existingHighestAccessLevelIndexedByLocaleCode), $affectedLocalesCodes);
        $this->revokeAccesses($removedLocaleCodes, $group);

        $this->updateAccesses($highestAccessLevelIndexedByLocaleCode, $existingHighestAccessLevelIndexedByLocaleCode, $group);
    }

    private function getUserGroup(string $groupName): GroupInterface
    {
        $group = $this->groupRepository->findOneByIdentifier($groupName);

        if (null === $group) {
            throw new \LogicException('User group not found');
        }

        return $group;
    }

    /**
     * @param array{
     *      edit: array{
     *          all: bool,
     *          identifiers: string[],
     *      },
     *      view: array{
     *          all: bool,
     *          identifiers: string[],
     *      }
     * } $permissions
     */
    private function updateDefaultPermissions(GroupInterface $group, array $permissions): void
    {
        $defaultPermissions = $group->getDefaultPermissions();

        $currentHighestAll = $this->getCurrentHighestAll($defaultPermissions);
        $submittedHighestAll = $this->getSubmittedHighestAll($permissions);

        if ($currentHighestAll === $submittedHighestAll) {
            return;
        }

        $group->setDefaultPermission(
            self::DEFAULT_PERMISSION_VIEW,
            in_array($submittedHighestAll, [self::PERMISSION_EDIT, self::PERMISSION_VIEW])
        );
        $group->setDefaultPermission(
            self::DEFAULT_PERMISSION_EDIT,
            $submittedHighestAll === self::PERMISSION_EDIT
        );

        $this->groupSaver->save($group);
    }

    /**
     * @param array<string, bool>|null $defaultPermissions
     */
    private function getCurrentHighestAll(?array $defaultPermissions): ?string
    {
        if (true === ($defaultPermissions[self::DEFAULT_PERMISSION_EDIT] ?? null)) {
            return self::PERMISSION_EDIT;
        }

        if (true === ($defaultPermissions[self::DEFAULT_PERMISSION_VIEW] ?? null)) {
            return self::PERMISSION_VIEW;
        }

        return null;
    }

    /**
     * @param array{
     *      edit: array{
     *          all: bool,
     *          identifiers: string[],
     *      },
     *      view: array{
     *          all: bool,
     *          identifiers: string[],
     *      }
     * } $permissions
     */
    private function getSubmittedHighestAll(array $permissions): ?string
    {
        if (true === $permissions['edit']['all']) {
            return self::PERMISSION_EDIT;
        }

        if (true === $permissions['view']['all']) {
            return self::PERMISSION_VIEW;
        }

        return null;
    }

    /**
     * @param array{
     *      edit: array{
     *          all: bool,
     *          identifiers: string[],
     *      },
     *      view: array{
     *          all: bool,
     *          identifiers: string[],
     *      }
     * } $permissions
     *
     * @return string[]
     */
    private function getAffectedLocalesCodes(array $permissions): array
    {
        if ($permissions['edit']['all'] || $permissions['view']['all']) {
            return $this->getAllActiveLocalesCodes->execute();
        }

        return array_values(array_unique(array_merge(
            $permissions['edit']['identifiers'],
            $permissions['view']['identifiers'],
        )));
    }

    /**
     * @param string[] $affectedLocalesCodes
     * @param array{
     *      edit: array{
     *          all: bool,
     *          identifiers: string[],
     *      },
     *      view: array{
     *          all: bool,
     *          identifiers: string[],
     *      }
     * } $permissions
     *
     * @return array<string, string>
     */
    private function getHighestAccessLevelIndexedByLocaleCode(array $affectedLocalesCodes, array $permissions): array
    {
        $highestAccessLevelIndexedByLocaleCode = [];

        foreach ($affectedLocalesCodes as $code) {
            $highestAccessLevelIndexedByLocaleCode[$code] = $this->getHighestAccessLevelFromPermissions($code, $permissions);
        }

        return $highestAccessLevelIndexedByLocaleCode;
    }

    /**
     * @param array{
     *      edit: array{
     *          all: bool,
     *          identifiers: string[],
     *      },
     *      view: array{
     *          all: bool,
     *          identifiers: string[],
     *      }
     * } $permissions
     */
    private function getHighestAccessLevelFromPermissions(string $localeCode, array $permissions): string
    {
        if (true === $permissions['edit']['all'] || in_array($localeCode, $permissions['edit']['identifiers'], true)) {
            return self::PERMISSION_EDIT;
        }

        if (true === $permissions['view']['all'] || in_array($localeCode, $permissions['view']['identifiers'], true)) {
            return self::PERMISSION_VIEW;
        }

        throw new \LogicException('Locale code is not covered by submitted permissions');
    }

    /**
     * @param string[] $removedLocaleCodes
     * @param GroupInterface $group
     */
    private function revokeAccesses(array $removedLocaleCodes, GroupInterface $group): void
    {
        foreach ($removedLocaleCodes as $localeCode) {
            $locale = $this->getActiveLocaleReferenceFromCode->execute($localeCode);
            if (null === $locale) {
                continue;
            }
            $this->localeAccessManager->revokeGroupAccess($locale, $group);
        }
    }

    /**
     * @param array<string, string> $highestAccessLevelIndexedByLocaleCode
     * @param array<string, string> $existingHighestAccessLevelIndexedByLocaleCode
     */
    private function updateAccesses(
        array $highestAccessLevelIndexedByLocaleCode,
        array $existingHighestAccessLevelIndexedByLocaleCode,
        GroupInterface $group
    ): void {
        foreach ($highestAccessLevelIndexedByLocaleCode as $localeCode => $newLevel) {
            $existingLevel = $existingHighestAccessLevelIndexedByLocaleCode[$localeCode] ?? null;

            if ($existingLevel !== $newLevel) {
                $locale = $this->getActiveLocaleReferenceFromCode->execute($localeCode);
                if (null === $locale) {
                    continue;
                }
                $this->localeAccessManager->grantAccess($locale, $group, $newLevel);
            }
        }
    }
}
