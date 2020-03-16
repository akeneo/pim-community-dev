<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command deletes entries from the completeness table which are related
 * of products that do not exist anymore in the product or product model table.
 *
 * @author    Grégoire HUBERT <gregoire.hubert@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class CleanCompletenessForNonExistingProducts extends Command
{ 
    protected static $defaultName = 'pim:completeness:clean';

    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        parent::__construct();
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Clean orphan completeness entries.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sql = <<<SQL
DELETE FROM pim_catalog_completeness AS pcc
WHERE pcc.product_id NOT IN (SELECT id FROM pim_catalog_product AS pcp)
SQL;
        $output->writeln('<info>Cleaning orphans from completenesses table...</info>');
        $stmt = $this->connection->query($sql);
        $output->writeln(sprintf('<fg=white;options=bold>%d rows</> deleted from table "%s.pim_catalog_completeness".', $stmt->rowCount(), $this->connection->getDatabase()));

        return 0;
    }
}

