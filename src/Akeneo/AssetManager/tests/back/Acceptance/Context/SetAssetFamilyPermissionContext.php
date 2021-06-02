<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Acceptance\Context;

use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyHandler;
use Akeneo\AssetManager\Application\AssetFamilyPermission\SetPermissions\SetAssetFamilyPermissionsCommand;
use Akeneo\AssetManager\Application\AssetFamilyPermission\SetPermissions\SetAssetFamilyPermissionsHandler;
use Akeneo\AssetManager\Application\AssetFamilyPermission\SetPermissions\SetUserGroupPermissionCommand;
use Akeneo\AssetManager\Common\Fake\InMemoryAssetFamilyPermissionRepository;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Permission\RightLevel;
use Akeneo\AssetManager\Domain\Model\Permission\UserGroupIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyPermissionRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
final class SetAssetFamilyPermissionContext implements Context
{
    private const USER_GROUPS = ['IT support' => 154, 'Catalog Manager' => 122];
    private const ASSET_FAMILY_IDENTIFIER = 'designer';

    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private AssetFamilyPermissionRepositoryInterface $assetFamilyPermissionRepository;

    private SetAssetFamilyPermissionsHandler $setAssetFamilyPermissionsHandler;

    private ExceptionContext $exceptionContext;

    private ValidatorInterface $validator;

    private CreateAssetFamilyHandler $createAssetFamilyHandler;

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AssetFamilyPermissionRepositoryInterface $assetFamilyPermissionRepository,
        SetAssetFamilyPermissionsHandler $setAssetFamilyPermissionsHandler,
        ExceptionContext $exceptionContext,
        ValidatorInterface $validator,
        CreateAssetFamilyHandler $createAssetFamilyHandler
    ) {
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->assetFamilyPermissionRepository = $assetFamilyPermissionRepository;
        $this->setAssetFamilyPermissionsHandler = $setAssetFamilyPermissionsHandler;
        $this->exceptionContext = $exceptionContext;
        $this->validator = $validator;
        $this->createAssetFamilyHandler = $createAssetFamilyHandler;
    }

    /**
     * @Given /^an asset family without permissions$/
     */
    public function aAssetFamilyWithoutPermissions()
    {
        $createCommand = new CreateAssetFamilyCommand(self::ASSET_FAMILY_IDENTIFIER, [], [], []);

        $violations = $this->validator->validate($createCommand);
        if ($violations->count() > 0) {
            throw new \LogicException(sprintf('Cannot create asset family: %s', $violations->get(0)->getMessage()));
        }

        ($this->createAssetFamilyHandler)($createCommand);
    }

    /**
     * @When /^the user sets the following permissions for the asset family:$/
     */
    public function theUserSetsTheFollowingPermissionsForTheAssetFamily(TableNode $userGroupPermissions)
    {
        $permissionsByUserGroupCommands = [];
        foreach ($userGroupPermissions->getColumnsHash() as $userGroupPermission) {
            $command = new SetUserGroupPermissionCommand(
                self::USER_GROUPS[$userGroupPermission['user_group_identifier']],
                $userGroupPermission['right_level']
            );
            $permissionsByUserGroupCommands[] = $command;
        }

        $setPermissionsCommand = new SetAssetFamilyPermissionsCommand(
            self::ASSET_FAMILY_IDENTIFIER,
            $permissionsByUserGroupCommands
        );

        try {
            ($this->setAssetFamilyPermissionsHandler)($setPermissionsCommand);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @Then /^there should be a \'([^\']*)\' permission right for the user group \'([^\']*)\' on the asset family$/
     */
    public function thereShouldBeAPermissionRightForTheUserGroupOnTheAssetFamily($rightLevel, $userGroupName)
    {
        $userGroupIdentifier = self::USER_GROUPS[$userGroupName];

        $userGroupIdentifier = UserGroupIdentifier::fromInteger($userGroupIdentifier);
        $rightLevel = RightLevel::fromString($rightLevel);

        $hasPermission = $this->assetFamilyPermissionRepository->hasPermission(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            $userGroupIdentifier,
            $rightLevel
        );

        Assert::assertTrue($hasPermission);
    }

    /**
     * @Then /^the user has the following rights:$/
     */
    public function theUserHasTheFollowingRights()
    {
        //only used in the frontend part
    }
}
