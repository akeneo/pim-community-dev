<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BackoffElasticSearchStateHandler
{
    public const RETRY_COUNTER = 10;
    public const INITIAL_WAIT_DELAY = 10;
    public const BACKOFF_LOGARITHMIC_INCREMENT = 2;

    public function doIndex(iterable $chunkedCodes, ProgressBar $progressBar, \Closure $codesEsHandler, OutputInterface $output): int
    {
        $indexedCount = 0;

        $progressBar->start();
        foreach ($chunkedCodes as $codes) {
            $treatedBachSize= count($codes);

            $this->bulkExecute($codes, $output, $codesEsHandler);

            $progressBar->advance($treatedBachSize);
        }
        $progressBar->finish();

        return $indexedCount;
    }

    protected function resetState(): array
    {
        $backOverheat = false;
        $retryCounter = self::RETRY_COUNTER;
        $waitDelay = self::INITIAL_WAIT_DELAY;
        return array($backOverheat, $retryCounter, $waitDelay);
    }

    public  function bulkIndex($codes, OutputInterface $output, \Closure $codesEsHandler):int
    {
        $batchSize = count($codes);
        $indexed = 0;
        list($backOverheat, $retryCounter, $waitDelay) = $this->resetState();

        do {
            $batchEsCodes = $codes;
            if ($backOverheat) {
                $output->writeln("Sleeping before retry due to back pressure {$waitDelay} seconds, with batch size of {$batchSize} \n");
                sleep($waitDelay);
                $batchEsCodes = array_slice($codes, 0, $batchSize);
            }

            try {
                $codesEsHandler($batchEsCodes);
                array_splice($codes, 0, $batchSize);
                list($backOverheat, $retryCounter, $waitDelay) = $this->resetState();
                $indexed += count($batchEsCodes);
            } catch (BadRequest400Exception $e) {
                if ($e->getCode() == Response::HTTP_TOO_MANY_REQUESTS) {
                    $backOverheat = true;
                    $retryCounter--;
                    $waitDelay = $waitDelay + self::INITIAL_WAIT_DELAY; //Heuristic: linear increment
                    $batchSize = intdiv($batchSize, self::BACKOFF_LOGARITHMIC_INCREMENT); //Heuristic: logarithmics decrement
                }
            }
        } while (($backOverheat && $retryCounter) || count($codes));

        if ($backOverheat && isset($e)) {
            throw $e;
        }
        return $indexed;
    }
}
