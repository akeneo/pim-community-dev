<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\User;

use Akeneo\Connectivity\Connection\Application\User\CreateUserGroupInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateUserGroup implements CreateUserGroupInterface
{
    private const APP_USER_GROUP_TYPE = 'app';
    private SimpleFactoryInterface $userGroupFactory;
    private ObjectUpdaterInterface $userGroupUpdater;
    private SaverInterface $userGroupSaver;
    private ValidatorInterface $validator;

    public function __construct(
        SimpleFactoryInterface $userGroupFactory,
        ObjectUpdaterInterface $userGroupUpdater,
        SaverInterface $userGroupSaver,
        ValidatorInterface $validator
    ) {
        $this->userGroupFactory = $userGroupFactory;
        $this->userGroupUpdater = $userGroupUpdater;
        $this->userGroupSaver = $userGroupSaver;
        $this->validator = $validator;
    }

    public function execute(string $groupName): GroupInterface
    {
        /** @var GroupInterface $group */
        $group = $this->userGroupFactory->create();
        $this->userGroupUpdater->update($group, ['name' => $groupName, 'type' => self::APP_USER_GROUP_TYPE]);

        $errors = $this->validator->validate($group);
        if (0 < count($errors)) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }

            throw new \LogicException('The user group creation failed :\n' . implode('\n', $errorMessages));
        }

        $this->userGroupSaver->save($group);

        return $group;
    }
}
