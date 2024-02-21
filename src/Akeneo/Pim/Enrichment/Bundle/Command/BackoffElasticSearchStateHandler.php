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
    const MAX_RETRY_COUNTER = 10;
    const BACKOFF_LOGARITHMIC_INCREMENT = 2;

    private int $maxNumberRetry;
    private int $backoffLogarithmicIncrement;

    public function __construct(int $retryCounter=self::MAX_RETRY_COUNTER, int $backoffLogarithmicIncrement=self::BACKOFF_LOGARITHMIC_INCREMENT)
    {
        $this->maxNumberRetry = $retryCounter;
        $this->backoffLogarithmicIncrement = $backoffLogarithmicIncrement;
    }

    public function bulkExecute(array $codes, BulkEsHandlerInterface $codesEsHandler):int
    {
        return $this->executeAttempt([$codes], $codesEsHandler, 0);
    }

    /**
     * batchOfCodes is an array of array. Each sub-array represents a batch of codes to index.
     * [
     *     ['code_1', 'code_2'],
     *     ['code_3', 'code_4'],
     * ]
     **/
    private function executeAttempt(array $batchOfCodes, BulkEsHandlerInterface $codesEsHandler, int $numberRetry): int
    {
        $treated = 0;
        foreach ($batchOfCodes as $codes) {
            try {
                $treated+=$codesEsHandler->bulkExecute($codes);
            } catch (BadRequest400Exception $e) {
                if ($e->getCode() == Response::HTTP_TOO_MANY_REQUESTS  && $numberRetry < $this->maxNumberRetry) {
                    $batchSize = intdiv(count($codes), $this->backoffLogarithmicIncrement);
                    $smallerBatchOfCodes = array_chunk($codes, $batchSize);
                    $treated+=$this->executeAttempt($smallerBatchOfCodes, $codesEsHandler, ++$numberRetry);
                } else {
                    throw $e;
                }
            }
        }
        return $treated;
    }
}
