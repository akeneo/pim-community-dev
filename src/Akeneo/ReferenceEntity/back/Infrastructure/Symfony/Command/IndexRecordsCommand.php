<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Symfony\Command;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command indexes all the records loaded in the database
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexRecordsCommand extends ContainerAwareCommand
{
    public const INDEX_RECORDS_COMMAND_NAME = 'akeneo:reference-entity:index-records';
    public const NAME = 'akeneo:reference-entity:index-records';
    private const ERROR_CODE_USAGE = 1;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::INDEX_RECORDS_COMMAND_NAME)
            ->addArgument(
                'reference_entity_codes',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'List of reference entity codes to index',
                []
            )
            ->addOption(
                'all',
                true,
                InputOption::VALUE_NONE,
                'Index all existing records into Elasticsearch'
            )
            ->setDescription('Index all the records belonging to the given reference entities.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkRecordIndexExists();

        $isIndexAll = $input->getOption('all');
        $referenceEntityCodes = $input->getArgument('reference_entity_codes');

        if ($isIndexAll) {
            $this->indexAll($output);
        } elseif (0 < count($referenceEntityCodes)) {
            $this->indexByReferenceEntity($referenceEntityCodes, $output);
        } else {
            $output->writeln('<error>Please specify a list of reference entity codes to index or use the flag --all to index all records</error>');

            return self::ERROR_CODE_USAGE;
        }
    }

    /**
     * @throws \RuntimeException
     */
    private function checkRecordIndexExists()
    {
        $recordClient = $this->getContainer()->get('akeneo_referenceentity.client.record');
        if (!$recordClient->hasIndex()) {
            throw new \RuntimeException(
                sprintf(
                    'The index "%s" does not exist in Elasticsearch.',
                    $this->getContainer()->getParameter('record_index_name')
                )
            );
        }
    }

    /**
     * @param OutputInterface $output
     *
     */
    protected function indexAll(OutputInterface $output): void
    {
        $referenceEntityRepository = $this->getContainer()->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $recordIndexer = $this->getContainer()->get('akeneo_referenceentity.infrastructure.search.elasticsearch.record_indexer');
        $allReferenceEntities = $referenceEntityRepository->all();
        $count = 0;
        foreach ($allReferenceEntities as $referenceEntity) {
            /** @var ReferenceEntity $referenceEntity */
            $recordIndexer->indexByReferenceEntity($referenceEntity->getIdentifier());
            $count++;
        }

        $output->writeln(sprintf('<info>The records of %d reference entities have been indexed.</info>', $count));
    }

    /**
     * @param string[] $referenceEntityCodes
     */
    private function indexByReferenceEntity(array $referenceEntityCodes, OutputInterface $output): void
    {
        $existingReferenceEntityCodes = $this->getExistingReferenceEntityCodes($referenceEntityCodes, $output);

        $recordIndexer = $this->getContainer()->get('akeneo_referenceentity.infrastructure.search.elasticsearch.record_indexer');
        foreach ($existingReferenceEntityCodes as $i => $referenceEntityIdentifier) {
            $output->writeln(sprintf('<info>Indexing the records of "%s".</info>', $referenceEntityCodes[$i]));
            $recordIndexer->indexByReferenceEntity($referenceEntityIdentifier);
        }
    }

    /**
     * @param String[] $referenceEntityCodes
     *
     * @return ReferenceEntityIdentifier[]
     */
    private function getExistingReferenceEntityCodes(array $referenceEntityCodes, OutputInterface $output): array
    {
        $existsReferenceEntity = $this
            ->getContainer()
            ->get('akeneo_referenceentity.infrastructure.persistence.query.reference_entity_exists');
        $existingReferenceEntityCodes = [];
        foreach ($referenceEntityCodes as $referenceEntityCode) {
            if ($existsReferenceEntity->withIdentifier(ReferenceEntityIdentifier::fromString($referenceEntityCode))) {
                $existingReferenceEntityCodes[] = ReferenceEntityIdentifier::fromString($referenceEntityCode);
            } else {
                $output->writeln(
                    sprintf('<info>Skip "%s", this reference entity does not exist.</info>',
                        ReferenceEntityIdentifier::fromString($referenceEntityCode))
                );
            }
        }

        return $existingReferenceEntityCodes;
    }
}
