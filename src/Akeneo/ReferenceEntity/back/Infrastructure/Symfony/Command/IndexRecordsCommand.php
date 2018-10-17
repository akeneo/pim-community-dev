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
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command indexes all the records loaded in the database
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexRecordsCommand extends ContainerAwareCommand
{
    private const INDEX_RECORDS_COMMAND_NAME = 'akeneo:reference-entity:index-records';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::INDEX_RECORDS_COMMAND_NAME)
            ->setDescription('Index all the records');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
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
}
