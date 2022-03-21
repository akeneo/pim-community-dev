<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\Command;

use Akeneo\Platform\Bundle\FrameworkBundle\Logging\ContextLogProcessor;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Factory\ConfigurationFactory;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Model\EntityIndexConfiguration;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Service\DiffResults;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Query\GenericEntityESIndexFinder;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Query\GenericEntityMySQLIndexFinder;

use Doctrine\DBAL\Connection;
use Elasticsearch\ClientBuilder;
use Jfcherng\Diff\DiffHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class CompareDiffEsToMySQLCommand extends Command
{
    private const DIFF_MODE = 'JsonText';
    private const SEPARATOR = '_';
    private const EXTENSION = '.jsonl';

    protected static $defaultName = 'pimee:migration:diff';
    protected static $defaultDescription = 'Check before migration between Flexibility to Serenity the consistency of MySQL and Elasticsearch';

    private array $hosts;
    private string $filenameSource;
    private string $storeFiles;
    private GenericEntityMySQLIndexFinder $searchMySql;
    private GenericEntityESIndexFinder $searchEs;

    public function __construct(
        private Connection $connection,
                               $hosts,
        private LoggerInterface $logger,
                            $storeFiles,
        private ContextLogProcessor $contextLogProcessor
    )
    {
        parent::__construct(self::$defaultName);

        $this->hosts = is_string($hosts) ? [$hosts] : $hosts;
        $clientBuilder = new ClientBuilder();
        $esClient = $clientBuilder->setHosts($this->hosts)->build();
        $this->searchMySql = new GenericEntityMySQLIndexFinder($this->connection);
        $this->searchEs = new GenericEntityESIndexFinder($esClient);
        $this->storeFiles = $storeFiles;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->notice('Begin index comparison');
        $folder = $this->initFolder($this->storeFiles);
        $indexComparisonStatusList = [];
        $definedIndexSource = ConfigurationFactory::initConfigurationList();

        foreach ($definedIndexSource as $indexName => $indexMappingConfiguration) {
            $this->contextLogProcessor->insertContext("index",$indexName);

            $results = $this->readMySQLData($indexMappingConfiguration->mySql);
            $filenameSourceDB = $this->dumpItemToJsonFiles($results, $folder);

            $results = $this->readEsData($indexMappingConfiguration->elasticsearch, $indexName);
            $filenameSourceES = $this->dumpItemToJsonFiles($results, $folder);

            $status = $this->compareJsonFiles($filenameSourceDB, $filenameSourceES, $indexName);
            $indexComparisonStatusList[] = $status;
        }

        foreach($indexComparisonStatusList as $out){
            if($out > 0){
                return Command::FAILURE;
            }
        }
        return Command::SUCCESS;
    }

    public function readMySQLData(EntityIndexConfiguration $entityIndexConfiguration): \Traversable
    {
        $this->filenameSource = '/' . $entityIndexConfiguration->getSourceName() . self::SEPARATOR . $entityIndexConfiguration->getTableName();
        return $this->searchMySql->findAllByOrder($entityIndexConfiguration);
    }

    public function readEsData(EntityIndexConfiguration $entityIndexConfiguration, string $indexName): \Traversable
    {
        $this->filenameSource = '/' . $entityIndexConfiguration->getSourceName() . self::SEPARATOR . $indexName ;
        return $this->searchEs->findAllByOrder($entityIndexConfiguration);
    }

    public function dumpItemToJsonFiles(\Traversable $items, string $folder): string
    {
        touch($folder . $this->filenameSource . self::EXTENSION);
        foreach ($items as $it => $data) {
            file_put_contents($folder . $this->filenameSource . self::EXTENSION, json_encode($data) . "\n", FILE_APPEND);
        }
        return $folder . $this->filenameSource . self::EXTENSION;
    }

    public function compareJsonFiles(string $mysqlFiles, string $esFiles, string $indexName): int
    {
        $differOptions = [
            'context' => 0, // show how many neighbor lines
            'ignoreCase' => true, // ignore case difference
            'ignoreWhitespace' => true // ignore whitespace difference
        ];
        $rendererOptions = [
            'jsonEncodeFlags' => \JSON_PRETTY_PRINT];

        $line = DiffHelper::calculateFiles($mysqlFiles, $esFiles, self::DIFF_MODE, $differOptions, $rendererOptions);
        $resultsDiff = DiffResults::exploitDiffHelperResults($line);

        $this->logger->notice($indexName, [
            'missing_lines'=>count($resultsDiff->missingLines),
            'lines_to_delete'=>count($resultsDiff->lines2Delete),
            'obsolete_lines'=>count($resultsDiff->obsoleteLines)]);

        return count($resultsDiff->missingLines)+count($resultsDiff->lines2Delete)+count($resultsDiff->obsoleteLines);
    }

    public function initFolder(string $filename): string
    {
        $folder = $filename . '/es_mysql_diff/' . uniqid();
        mkdir($folder, 0770, true);
        return $folder;
    }
}