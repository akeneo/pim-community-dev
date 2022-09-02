<?php
namespace App\Command\Uptime;

use Google\ApiCore\ApiException;
use Google\Cloud\Monitoring\V3\Aggregation;
use Google\Cloud\Monitoring\V3\Aggregation\Aligner;
use Google\Cloud\Monitoring\V3\Aggregation\Reducer;
use Google\Cloud\Monitoring\V3\ListTimeSeriesRequest\TimeSeriesView;
use Google\Cloud\Monitoring\V3\MetricServiceClient;
use Google\Cloud\Monitoring\V3\TimeInterval;
use Google\Cloud\Monitoring\V3\UptimeCheckServiceClient;
use Google\Protobuf\Duration;
use Google\Protobuf\Timestamp;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Get extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'deployment:uptime:get';
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logLevel = getenv('LOG_LEVEL') ?: 'WARNING';
        $projectId = getenv('PROJECT') ?: 'akecld-saas-prod';
        
        $metrics = new MetricServiceClient(['projectId' => $projectId]);
        $projectName = $metrics->projectName($projectId);

        try {
            $filter = 'metric.type="monitoring.googleapis.com/uptime_check/check_passed" resource.type="uptime_url"';
        
            $minutesAgo = 60*24*7*6;

            //max is 6 weeks ago
            $startTime = new Timestamp();
            $startTime->setSeconds(time() - (60 * $minutesAgo));
            $endTime = new Timestamp();
            $endTime->setSeconds(time());
        
            $interval = new TimeInterval();
            $interval->setStartTime($startTime);
            $interval->setEndTime($endTime);
        
            $alignmentPeriod = new Duration();
            $alignmentPeriod->setSeconds(3600);
        
            $aggregation = new Aggregation();
            $aggregation->setAlignmentPeriod($alignmentPeriod);
            $aggregation->setGroupByFields(['metric.label."check_id"']);
            $aggregation->setCrossSeriesReducer(Reducer::REDUCE_SUM);
            $aggregation->setPerSeriesAligner(Aligner::ALIGN_COUNT);
        
            $aggregationOk = new Aggregation();
            $aggregationOk->setAlignmentPeriod($alignmentPeriod);
            $aggregationOk->setGroupByFields(['metric.label."check_id"']);
            $aggregationOk->setCrossSeriesReducer(Reducer::REDUCE_SUM);
            $aggregationOk->setPerSeriesAligner(Aligner::ALIGN_COUNT_TRUE);
        
        
            $view = TimeSeriesView::FULL;
        
            // List all uptimecheck metrics and sum it by check_id
            // If a metric only have zeros
            $this->logger->info("Get aggregated uptimecheck metrics");
            $resultTotal = $metrics->listTimeSeries(
                $projectName,
                $filter,
                $interval,
                $view,
                ['aggregation' => $aggregation]);
        
        
        
            $result = [];
        
            foreach ($resultTotal->iterateAllElements() as $timeSeries) {
                $checkId = $timeSeries->getMetric()->getLabels()["check_id"];
        
                $result[$checkId] = [];
                foreach ($timeSeries->getPoints() as $point) {
                    $value = $point->getValue()->getInt64Value();
                    $timestampStart = $point->getInterval()->getStartTime()->getSeconds();
        
                    $date = new \DateTime();
                    $date->setTimezone(new \DateTimeZone('Europe/Paris'));
                    $date->setTimestamp($timestampStart);
        
                    $dw = $date->format("w");
                    $column = $date->format("Y-m-d");
                    $hour = $date->format("G");
                    if ($dw == "3" && ($hour >= 18 ) && ($hour < 21 )) {
                        if (!isset($result[$checkId][$column])) {
                            $result[$checkId][$column] = ["total" => 0, "ok" => 0];
                        }
                        $result[$checkId][$column]["total"] += $value;
                    }
                }
            }
            unset($resultTotal);

            $resultOk = $metrics->listTimeSeries(
                $projectName,
                $filter,
                $interval,
                $view,
                ['aggregation' => $aggregationOk]);
        
            foreach ($resultOk->iterateAllElements() as $timeSeries) {
                $checkId = $timeSeries->getMetric()->getLabels()["check_id"];
        
                if (isset($result[$checkId])) {
                    foreach ($timeSeries->getPoints() as $point) {
                        $value = $point->getValue()->getInt64Value();
                        $timestampStart = $point->getInterval()->getStartTime()->getSeconds();
        
                        $date = new \DateTime();
                        $date->setTimezone(new \DateTimeZone('Europe/Paris'));
                        $date->setTimestamp($timestampStart);
        
                        $dw = $date->format("w");
                        $column = $date->format("Y-m-d");
                        $hour = $date->format("G");
                        if ($dw == "3" && isset($result[$checkId][$column]) && ($hour >= 18 ) && ($hour < 21 )) {
                            $result[$checkId][$column]["ok"] += $value;
                        }
                    }
        
                    foreach ($result[$checkId] as $dataLabel=>$resultOkToto) {
                        echo $checkId . ";" . $dataLabel . ";" . $resultOkToto["total"] . ";" . $resultOkToto["ok"] . ";"  . PHP_EOL;
                    }
        
                }
            }
        } finally {
            $metrics->close();
        }

        return Command::SUCCESS;
    }
}
