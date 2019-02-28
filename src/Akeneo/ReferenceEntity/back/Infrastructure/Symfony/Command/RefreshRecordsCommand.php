<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Symfony\Command;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindIdentifiersForQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * This command refreshes all the records after to have a record linked, all records of a reference entity or attribute options linked deleted.
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshRecordsCommand extends ContainerAwareCommand
{
    private const REFRESH_ALL_RECORDS_COMMAND_NAME = 'akeneo:reference-entity:refresh-all-records';
    private const BATCH_SIZE = 100;

    /** @var FindIdentifiersForQueryInterface */
    private $findIdentifiersForQuery;

    /** @var RecordRepositoryInterface  */
    private $recordRepository;

    public function __construct(FindIdentifiersForQueryInterface $findIdentifiersForQuery, RecordRepositoryInterface $recordRepository)
    {
        parent::__construct(self::REFRESH_ALL_RECORDS_COMMAND_NAME);

        $this->findIdentifiersForQuery = $findIdentifiersForQuery;
        $this->recordRepository = $recordRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::REFRESH_ALL_RECORDS_COMMAND_NAME)
            ->setDescription('Resets all records that have the record, the records of a reference entity or the attribute option deleted.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $nbRecordsRefreshed = $page = 0;
        $size = self::BATCH_SIZE;
        $countByReferenceEntity = $this->recordRepository->countByReferenceEntity(
            ReferenceEntityIdentifier::fromString('city')
        );
        $progressBar = new ProgressBar($output, $countByReferenceEntity);
        $progressBar->start();

        while ($nbRecordsRefreshed < $countByReferenceEntity) {
            $query = RecordQuery::createFromNormalized([
               'locale' => 'en_US',
               'channel' => 'ecommerce',
               'size' => $size,
               'page' => $page,
               'filters' => [
                   [
                       'field' => 'reference_entity',
                       'operator' => '=',
                       'value' => 'city',
                       'context' => []
                   ]
               ]
           ]);
            $recordIdentifiersResult = ($this->findIdentifiersForQuery)($query);

            foreach ($recordIdentifiersResult->identifiers as $recordIdentifier) {
                $record = $this->recordRepository->getByIdentifier(RecordIdentifier::fromString($recordIdentifier));
                $this->recordRepository->update($record);
                $nbRecordsRefreshed++;
                if ($nbRecordsRefreshed === $countByReferenceEntity) {
                    break;
                }

                $progressBar->advance();
            }

            if ($countByReferenceEntity - $nbRecordsRefreshed < self::BATCH_SIZE) {
                $size = $countByReferenceEntity - $nbRecordsRefreshed;
            }
            $page++;
        }

        $progressBar->finish();
    }
}
