<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Acceptance\Context;

use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\SetPermissions\SetReferenceEntityPermissionsCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\SetPermissions\SetReferenceEntityPermissionsHandler;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\SetPermissions\SetUserGroupPermissionCommand;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryReferenceEntityPermissionRepository;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Permission\RightLevel;
use Akeneo\ReferenceEntity\Domain\Model\Permission\UserGroupIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityPermissionRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
final class SetReferenceEntityPermissionContext implements Context
{
    private const USER_GROUPS = ['IT support' => 154, 'Catalog manager' => 122];
    private const REFERENCE_ENTITY_IDENTIFIER = 'designer';

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var InMemoryReferenceEntityPermissionRepository */
    private $referenceEntityPermissionRepository;

    /** @var SetReferenceEntityPermissionsHandler */
    private $setReferenceEntityPermissionsHandler;

    /** @var ExceptionContext */
    private $exceptionContext;

    public function __construct(
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        ReferenceEntityPermissionRepositoryInterface $referenceEntityPermissionRepository,
        SetReferenceEntityPermissionsHandler $setReferenceEntityPermissionsHandler,
        ExceptionContext $exceptionContext
    ) {
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->referenceEntityPermissionRepository = $referenceEntityPermissionRepository;
        $this->setReferenceEntityPermissionsHandler = $setReferenceEntityPermissionsHandler;
        $this->exceptionContext = $exceptionContext;
    }

    /**
     * @Given /^a reference entity without permissions$/
     */
    public function aReferenceEntityWithoutPermissions()
    {
        $this->referenceEntityRepository->create(
            ReferenceEntity::create(
                ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
                [],
                Image::createEmpty()
            )
        );
    }

    /**
     * @When /^the user sets the following permissions for the reference entity:$/
     */
    public function theUserSetsTheFollowingPermissionsForTheReferenceEntity(TableNode $userGroupPermissions)
    {
        $setPermissionsCommand = new SetReferenceEntityPermissionsCommand();
        $setPermissionsCommand->referenceEntityIdentifier = self::REFERENCE_ENTITY_IDENTIFIER;
        foreach ($userGroupPermissions->getColumnsHash() as $userGroupPermission) {
            $command = new SetUserGroupPermissionCommand();
            $command->userGroupIdentifier = self::USER_GROUPS[$userGroupPermission['user_group_identifier']];
            $command->rightLevel = $userGroupPermission['right_level'];
            $setPermissionsCommand->permissionsByUserGroup[] = $command;
        }

        try {
            ($this->setReferenceEntityPermissionsHandler)($setPermissionsCommand);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @Then /^there should be a \'([^\']*)\' permission right for the user group \'([^\']*)\' on the reference entity$/
     */
    public function thereShouldBeAPermissionRightForTheUserGroupOnTheReferenceEntity($rightLevel, $userGroupName)
    {
        $userGroupIdentifier = self::USER_GROUPS[$userGroupName];

        $userGroupIdentifier = UserGroupIdentifier::fromInteger($userGroupIdentifier);
        $rightLevel = RightLevel::fromString($rightLevel);

        $hasPermission = $this->referenceEntityPermissionRepository->hasPermission(
            ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
            $userGroupIdentifier,
            $rightLevel
        );

        Assert::assertTrue($hasPermission);
    }
}
