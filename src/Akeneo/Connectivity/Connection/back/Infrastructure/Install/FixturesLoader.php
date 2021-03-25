<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Install;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Types\Types;
use OAuth2\OAuth2;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FixturesLoader
{
    private $dbalConnection;
    private $fileStorer;
    private $validator;
    private $userFactory;
    private $userUpdater;
    private $userSaver;
    private $userRoleFactory;
    private $userRoleUpdater;
    private $userRoleSaver;
    private $userGroupFactory;
    private $userGroupUpdater;
    private $userGroupSaver;

    public function __construct(
        DbalConnection $dbalConnection,
        FileStorerInterface $fileStorer,
        ValidatorInterface $validator,
        SimpleFactoryInterface $userFactory,
        ObjectUpdaterInterface $userUpdater,
        SaverInterface $userSaver,
        SimpleFactoryInterface $userRoleFactory,
        ObjectUpdaterInterface $userRoleUpdater,
        SaverInterface $userRoleSaver,
        SimpleFactoryInterface $userGroupFactory,
        ObjectUpdaterInterface $userGroupUpdater,
        SaverInterface $userGroupSaver
    ) {
        $this->dbalConnection = $dbalConnection;
        $this->fileStorer = $fileStorer;
        $this->validator = $validator;
        $this->userFactory = $userFactory;
        $this->userUpdater = $userUpdater;
        $this->userSaver = $userSaver;
        $this->userRoleFactory = $userRoleFactory;
        $this->userRoleUpdater = $userRoleUpdater;
        $this->userRoleSaver = $userRoleSaver;
        $this->userGroupFactory = $userGroupFactory;
        $this->userGroupUpdater = $userGroupUpdater;
        $this->userGroupSaver = $userGroupSaver;
    }

    public function loadFixtures(): void
    {
        $roles = [
            'source' => $this->createUserRole(['role' => 'ROLE_API_SOURCE', 'label' => 'API Source']),
            'destination' => $this->createUserRole(['role' => 'ROLE_API_DESTINATION', 'label' => 'API Destination'])
        ];

        $groups = [
            'sap' => $this->createUserGroup(['name' => 'SAP Connection']),
            'alkemics' => $this->createUserGroup(['name' => 'Alkemics Connection']),
            'translations_com' => $this->createUserGroup(['name' => 'Translations.com Connection']),
            'magento' => $this->createUserGroup(['name' => 'Magento Connection']),
        ];

        // Magento Connection

        $username = 'magento_0000';
        $user = $this->createUser([
            'username' => $username,
            'password' => '2dpuj5tx4w4d',
            'roles' => [$roles['destination']->getRole()],
            'groups' => [$groups['magento']->getName()]
        ]);

        $clientId = $this->createClient([
            'label' => 'Magento',
            'random_id' => '16d23okvhfb44ccgo8s4wgoo8swocokcgsk0c0o4c084k00ks4',
            'secret' => '2crnhds1wx5wocwsg4sw0cgwo0w0sckwcokg8go4sck8c44cso',
        ]);

        $image = $this->uploadImage('magento.png');

        $this->createConnection([
            'client_id' => $clientId,
            'user_id' => $user->getId(),
            'code' => 'magento',
            'label' => 'Magento',
            'flow_type' => FlowType::DATA_DESTINATION,
            'image' => $image->getKey(),
            'auditable' => true,
        ]);

        // SAP Connection

        $username = 'sap_0000';
        $user = $this->createUser([
            'username' => $username,
            'password' => 'xjhsee5443qv',
            'roles' => [$roles['source']->getRole()],
            'groups' => [$groups['sap']->getName()]
        ]);

        $clientId = $this->createClient([
            'label' => 'SAP',
            'random_id' => '1dis30qkkhes08gocw4kcwgg8ccggo00wwc4044c8ckk4w8o0w',
            'secret' => '5atmpvrj81ogccgokksk4wgkwc4wkkgccsogowgwks4gc0wc48',
        ]);

        $image = $this->uploadImage('sap.png');

        $this->createConnection([
            'client_id' => $clientId,
            'user_id' => $user->getId(),
            'code' => 'sap',
            'label' => 'SAP',
            'flow_type' => FlowType::DATA_SOURCE,
            'image' => $image->getKey(),
            'auditable' => true,
        ]);

        // Alkemics Connection

        $username = 'alkemics_0000';
        $user = $this->createUser([
            'username' => $username,
            'password' => 'dvx9bjw5b923',
            'roles' => [$roles['source']->getRole()],
            'groups' => [$groups['alkemics']->getName()]
        ]);

        $clientId = $this->createClient([
            'label' => 'Alkemics',
            'random_id' => '5mztbe8339k4wsk8ow0wcsw8gwwoo0kss00c8gks4g4wccgow4',
            'secret' => 'a56yx4zji74k8gkko4o8w40wgsosk0g88g4sc8c4scw0okkks',
        ]);

        $image = $this->uploadImage('alkemics.png');

        $this->createConnection([
            'client_id' => $clientId,
            'user_id' => $user->getId(),
            'code' => 'alkemics',
            'label' => 'Alkemics',
            'flow_type' => FlowType::DATA_SOURCE,
            'image' => $image->getKey(),
            'auditable' => true,
        ]);

        // Translations.com Connection

        $username = 'translations_com_0000';
        $user = $this->createUser([
            'username' => $username,
            'password' => 'hpd63xahxbyg',
            'roles' => [$roles['source']->getRole()],
            'groups' => [$groups['translations_com']->getName()]
        ]);

        $clientId = $this->createClient([
            'label' => 'Translations.com',
            'random_id' => '5dyjeui4iwow4kcowogc008wwksgwc0kgc8sckkwgossso8scs',
            'secret' => '2tujtekr2bokscck8s8scwgos44ccoc88scsc00kosgo4ksks8',
        ]);

        $image = $this->uploadImage('translations_com.png');

        $this->createConnection([
            'client_id' => $clientId,
            'user_id' => $user->getId(),
            'code' => 'translations_com',
            'label' => 'Translations.com',
            'flow_type' => FlowType::OTHER,
            'image' => $image->getKey(),
            'auditable' => false,
        ]);
    }

    private function createUserRole(array $data): RoleInterface
    {
        $role = $this->userRoleFactory->create();

        $this->userRoleUpdater->update($role, $data);
        $this->validate($role);
        $this->userRoleSaver->save($role, ['is_fixture' => true]);

        return $role;
    }

    private function createUserGroup(array $data): GroupInterface
    {
        $group = $this->userGroupFactory->create();

        $this->userGroupUpdater->update($group, $data);
        $this->validate($group);
        $this->userGroupSaver->save($group);

        return $group;
    }

    private function createUser(array $data): UserInterface
    {
        /** @var UserInterface */
        $user = $this->userFactory->create();
        $user->defineAsApiUser();

        $this->userUpdater->update(
            $user,
            array_merge(
                [
                    'first_name' => $data['username'],
                    'last_name' => $data['username'],
                    'email' => sprintf('%s@example.com', $data['username']),
                ],
                $data
            )
        );
        $this->validate($user);
        $this->userSaver->save($user);

        return $user;
    }

    private function createClient(array $data): string
    {
        $this->dbalConnection->insert(
            'pim_api_client',
            array_merge(
                [
                    'allowed_grant_types' => [OAuth2::GRANT_TYPE_USER_CREDENTIALS, OAuth2::GRANT_TYPE_REFRESH_TOKEN],
                    'redirect_uris' => []
                ],
                $data
            ),
            [
                'allowed_grant_types' => Types::ARRAY,
                'redirect_uris' => Types::ARRAY
            ]
        );

        return $this->dbalConnection->lastInsertId();
    }

    private function createConnection(array $data): void
    {
        $this->dbalConnection->insert('akeneo_connectivity_connection', $data, ['auditable' => Types::BOOLEAN]);
    }

    private function uploadImage(string $file): FileInfoInterface
    {
        $rawFile = new \SplFileInfo(__DIR__ . '/../Symfony/Resources/fixtures/images/' . $file);

        return $this->fileStorer->store($rawFile, FileStorage::CATALOG_STORAGE_ALIAS);
    }

    private function validate(object $object): void
    {
        $violations = $this->validator->validate($object);

        if (0 < $violations->count()) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getMessage();
            }

            throw new \InvalidArgumentException(
                sprintf(
                    'Object "%s" is not valid, cf following constraint violations "%s"',
                    get_class($object),
                    implode(', ', $messages)
                )
            );
        }
    }
}
