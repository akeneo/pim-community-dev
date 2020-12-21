<?php

namespace Akeneo\SharedCatalog\Command;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Types\Types;
use OAuth2\OAuth2;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SharedCatalogFixturesCommand extends Command
{
    const FIXTURES_NAME = 'shared_catalog_fixtures';

    protected static $defaultName = 'akeneo:shared-catalog:fixtures';

    private $dbalConnection;
    private $validator;
    private $userFactory;
    private $userUpdater;
    private $userSaver;

    public function __construct(
        DbalConnection $dbalConnection,
        ValidatorInterface $validator,
        SimpleFactoryInterface $userFactory,
        ObjectUpdaterInterface $userUpdater,
        SaverInterface $userSaver
    ) {
        parent::__construct(null);
        $this->dbalConnection = $dbalConnection;
        $this->validator = $validator;
        $this->userFactory = $userFactory;
        $this->userUpdater = $userUpdater;
        $this->userSaver = $userSaver;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Load the fixtures of the shared catalog.')
            ->addOption('force')
            ->setHidden(true);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('force')) {
            throw new \InvalidArgumentException('Missing argument "--force"');
        }

        $this->createUser([
            'username' => 'julia',
            'password' => 'julia',
            'roles' => [
                'ROLE_ADMINISTRATOR',
            ],
            'groups' => [
                'IT support',
            ]
        ], false);

        $apiUser = $this->createUser([
            'username' => 'shared_catalog',
            'password' => 'shared_catalog',
            'roles' => [
                'ROLE_ADMINISTRATOR',
            ],
            'groups' => [
                'IT support',
            ]
        ], true);

        $clientId = $this->createClient([
            'label' => 'Shared Catalog',
            'random_id' => '5mztbe8339k4wsk8ow0wcsw8gwwoo0kss00c8gks4g4wccgow0',
            'secret' => 'a56yx4zji74k8gkko4o8w40wgsosk0g88g4sc8c4scw0okkk0',
        ]);

        $this->createConnection([
            'client_id' => $clientId,
            'user_id' => $apiUser->getId(),
            'code' => 'shared_catalog',
            'label' => 'Shared Catalog',
            'flow_type' => FlowType::DATA_DESTINATION,
            'image' => null,
            'auditable' => false,
        ]);

        $this->createSharedCatalog();
    }

    private function createUser(array $data, bool $isApiUser = false): UserInterface
    {
        /** @var UserInterface $user */
        $user = $this->userFactory->create();

        if ($isApiUser) {
            $user->defineAsApiUser();
        }

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

    private function createSharedCatalog(): void
    {
        $query = <<<SQL
    INSERT INTO akeneo_batch_job_instance
        (code, label, job_name, status, connector, raw_parameters, type)
    VALUES (
        'catalog1',
        'Catalog 1',
        'akeneo_shared_catalog',
        0,
        'Akeneo Shared Catalogs',
        'a:13:{s:8:"filePath";s:38:"/tmp/export_%job_label%_%datetime%.csv";s:9:"delimiter";s:1:";";s:9:"enclosure";s:1:""";s:10:"withHeader";b:1;s:14:"user_to_notify";N;s:21:"is_user_authenticated";b:0;s:16:"decimalSeparator";s:1:".";s:10:"dateFormat";s:10:"yyyy-MM-dd";s:10:"with_media";b:1;s:7:"filters";a:2:{s:4:"data";a:3:{i:0;a:3:{s:5:"field";s:7:"enabled";s:8:"operator";s:1:"=";s:5:"value";b:1;}i:1;a:4:{s:5:"field";s:12:"completeness";s:8:"operator";s:3:"ALL";s:5:"value";i:100;s:7:"context";a:1:{s:7:"locales";a:5:{i:0;s:5:"de_DE";i:1;s:5:"en_GB";i:2;s:5:"en_US";i:3;s:5:"es_ES";i:4;s:5:"fr_FR";}}}i:2;a:3:{s:5:"field";s:10:"categories";s:8:"operator";s:11:"IN CHILDREN";s:5:"value";a:1:{i:0;s:6:"master";}}}s:9:"structure";a:2:{s:5:"scope";s:9:"ecommerce";s:7:"locales";a:5:{i:0;s:5:"de_DE";i:1;s:5:"en_GB";i:2;s:5:"en_US";i:3;s:5:"es_ES";i:4;s:5:"fr_FR";}}}s:9:"publisher";s:17:"julia@example.com";s:10:"recipients";a:1:{i:0;a:1:{s:5:"email";s:16:"betty@akeneo.com";}}s:8:"branding";a:1:{s:5:"image";N;}}',
        'export'
    );
SQL;

        $this->dbalConnection->executeQuery($query);

        $query = <<<SQL
    INSERT INTO pimee_security_job_profile_access
        (job_profile_id, user_group_id, execute_job_profile, edit_job_profile)
    VALUES (
        (SELECT id FROM akeneo_batch_job_instance WHERE code = 'catalog1'),
        (SELECT id FROM oro_access_group WHERE name = 'All'),
        1,
        1
    );
SQL;

        $this->dbalConnection->executeQuery($query);
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
