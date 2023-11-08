<?php

namespace Akeneo\Platform\Bundle\InstallerBundle\Command;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Check attributes to find any custom ones that would be incompatible with Saas
 *
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CheckAttributesCompatibilityWithSaasCommand extends Command
{
    protected static $defaultName = 'pim:installer:check-attributes';
    protected static $defaultDescription = 'Check attributes to find any custom ones that would be incompatible with Saas';

    public function __construct(
        private Connection $connection
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Checking the attributes table');

        $invalidAttributes = $this->fetchInvalidAttributes();

        if (\count($invalidAttributes) > 0) {
            $output->writeln('Invalid attributes were found :');
            $table = new Table($output);

            $table->setHeaders(['Attribute Code', 'Attribute type', 'Backend Type'])->setRows([]);

            foreach ($invalidAttributes as $invalidAttribute) {
                $table->addRow([
                    $invalidAttribute['code'],
                    $invalidAttribute['attribute_type'],
                    $invalidAttribute['backend_type'],
                ]);
            }

            $table->render();

            return Command::FAILURE;
        }

        $output->writeln('All attributes are of valid types.');
        return Command::SUCCESS;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function fetchInvalidAttributes(): array
    {
        $sql = <<<SQL
SELECT code, attribute_type, backend_type
FROM pim_catalog_attribute
WHERE attribute_type NOT IN (:attributeTypes)
OR backend_type NOT IN (:backendTypes)
SQL;

        return $this->connection->fetchAllAssociative(
            $sql,
            [
                'attributeTypes' => AttributeTypes::attributeTypes(),
                'backendTypes' => AttributeTypes::backendTypes(),
            ],
            [
                'attributeTypes' => Connection::PARAM_STR_ARRAY,
                'backendTypes' => Connection::PARAM_STR_ARRAY,
            ]
        );
    }
}
