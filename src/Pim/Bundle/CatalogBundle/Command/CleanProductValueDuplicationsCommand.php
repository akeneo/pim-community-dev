<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * On ORM projects there is rare chances that several PHP processes create the same product value at the same time, for
 * example two import jobs running in parallel:
 *   1. Job A fetches a product
 *   2. Job B fetches the same product
 *   3. Both jobs set a value for the same attribute that were empty (so the product value doesn't exist yet in db)
 *   4. Job A persists the product, the new product value is inserted with id 32
 *   5. Job B persists the product, the new product value is inserted with id 33
 *
 * As there is currently no easy way to prevent that, here is a command that can search product value duplications and
 * delete them.
 * Two product values are considered as duplicated when they have the same attribute_id, entity_id, locale_code and
 * scope code.
 *
 * This is not a normal maintenance operation. This command should be used only when it's proven that the database has
 * already been corrupted and should not replace usual good practices and debugging.
 * The best known way to avoid this kind of issue is to correctly plan jobs that write data (imports and rules execution
 * mainly) to avoid overlapping, and plan it when the application use is the lowest.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CleanProductValueDuplicationsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     *
     * This command is relevant only on ORM projects.
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:product:clean-value-duplications')
            ->setDescription('Clean product value duplications that could have been inserted.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run')
            ->setHelp(<<<HELP
On ORM projects there is rare chances that several PHP processes create the same product value at the same time, for
example two import jobs running in parallel:
    1. Job A fetches a product
    2. Job B fetches the same product
    3. Both jobs set a value for the same attribute that were empty (so the product value doesn't exist yet in db)
    4. Job A persists the product, the new product value is inserted with id 32
    5. Job B persists the product, the new product value is inserted with id 33

As there is currently no easy way to prevent that, here is a command that can search product value duplications and
delete them.
Two product values are considered as duplicated when they have the same attribute_id, entity_id, locale_code and
scope code.

This is not a normal maintenance operation. This command should be used only when it's proven that the database has
already been corrupted and should not replace usual good practices and debugging.
The best known way to avoid this kind of issue is to correctly plan jobs that write data (imports and rules execution
mainly) to avoid overlapping, and plan it when the application use is the lowest.
HELP
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->getConnection();

        $output->writeln('Counting duplicated product values...');
        $sql = $this->getCountQuery();
        $searchResult = $connection->fetchColumn($sql);
        $output->writeln(sprintf('<info>%d</info> duplicated product values would be deleted.', $searchResult));

        if ($input->getOption('dry-run')) {
            return;
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            implode([
                '<comment>This operation will delete information from your database.',
                'It is recommended to use it carefully and to backup your database before performing it.</comment>',
                'Do you want to continue? [y/N]'
            ], "\n"),
            false
        );

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $output->writeln('Deleting duplicated product values...');
        $sql = $this->getDeleteQuery();
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $output->writeln(sprintf('<info>%d</info> duplicated product values have been deleted.', $stmt->rowCount()));
    }

    /**
     * @return string
     */
    protected function getCountQuery()
    {
        return 'SELECT COUNT(pv.id)'.$this->getMainQueryPart();
    }

    /**
     * @return string
     */
    protected function getDeleteQuery()
    {
        return 'DELETE pv'.$this->getMainQueryPart();
    }

    /**
     * @return string
     */
    protected function getMainQueryPart()
    {
        $tableName = $this->getTableName('pim_catalog.entity.product_value.class');

        return <<<MAIN_SQL
            FROM $tableName pv JOIN (
                SELECT COUNT(*) AS countPV, MAX(id) AS maxId, attribute_id, entity_id, locale_code, scope_code
                FROM $tableName
                GROUP BY attribute_id, entity_id, locale_code, scope_code
                HAVING countPV > 1
            ) AS pvGroup
            ON pv.attribute_id = pvGroup.attribute_id
            AND pv.entity_id = pvGroup.entity_id
            AND pv.locale_code <=> pvGroup.locale_code
            AND pv.scope_code <=> pvGroup.scope_code
            AND pv.id != pvGroup.maxId
MAIN_SQL;
    }

    /**
     * @return Connection
     */
    protected function getConnection()
    {
        return $this
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getConnection();
    }

    /**
     * @param string $entityParameter
     *
     * @return string
     */
    protected function getTableName($entityParameter)
    {
        return $this->getContainer()
            ->get('akeneo_storage_utils.doctrine.table_name_builder')
            ->getTableName($entityParameter);
    }
}
