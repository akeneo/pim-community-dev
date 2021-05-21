<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Elasticsearch\Common\Exceptions\ElasticsearchException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BackoffElasticSearchStateHandler
{
    const RETRY_COUNTER = 10;
    const BACKOFF_LOGARITHMIC_INCREMENT = 2;

    private int $retryCounter;
    private int $backoffLogarithmicIncrement;

    public function __construct(int $retryCounter=self::RETRY_COUNTER, int $backoffLogarithmicIncrement=self::BACKOFF_LOGARITHMIC_INCREMENT)
    {
        $this->retryCounter = $retryCounter;
        $this->backoffLogarithmicIncrement = $backoffLogarithmicIncrement;
    }

    protected function resetState(): array
    {
        return [false, $this->retryCounter];
    }

    public function bulkExecute(array $codes, BulkEsHandlerInterface $codesEsHandler):int
    {
        $initialBatchSize = count($codes);
        $batchSize = $initialBatchSize;
        $indexed = 0;
        [$backOverheat, $retryCounter] = $this->resetState();

        do {
            $batchEsCodes = $codes;
            if ($backOverheat) {
                $batchEsCodes = array_slice($codes, 0, $batchSize);
            }

            try {
                $codesEsHandler->bulkExecute($batchEsCodes);

                array_splice($codes, 0, $batchSize);
                list($backOverheat, $retryCounter) = $this->resetState();
                $batchSize=$initialBatchSize;
                $indexed += count($batchEsCodes);
            } catch (BadRequest400Exception $e) {
                if ($e->getCode() == Response::HTTP_TOO_MANY_REQUESTS) {
                    $retryCounter--;
                    $backOverheat = true;
                    $batchSize = intdiv($batchSize, $this->backoffLogarithmicIncrement); //Heuristic: logarithmics decrement
                } else {
                    throw $e;
                }
            }
        } while (($retryCounter > 0) && count($codes));

        if ($backOverheat && isset($e)) {
            throw $e;
        }
        return $indexed;
    }
}
