<?php

declare(strict_types=1);

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

class Clear extends Command
{
    protected static $defaultName = 'deployment:uptime:clear';
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
        parent::__construct();
    }

    private function getUptimeCheckName(string $projectId, string $uptimeCheckId) : string {
        return "projects/" . $projectId . "/uptimeCheckConfigs/" . $uptimeCheckId;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $projectId=getenv('PROJECT') ?: 'akecld-saas-dev';
        
        $metrics = new MetricServiceClient(['projectId' => $projectId]);
        $projectName = $metrics->projectName($projectId);
        
        $uptimeCheckClient = new UptimeCheckServiceClient(['projectId' => $projectId]);
        
        try {
            $filter = 'metric.type="monitoring.googleapis.com/uptime_check/check_passed" resource.type="uptime_url" metric.label."check_id"=monitoring.regex.full_match("pim(ci|up)-.*")';

            $minutesAgo = 60;
            $startTime = $this->getTimestamp($minutesAgo);
            $endTime = $this->getTimestamp();

            $interval = $this->getTimeInterval($startTime, $endTime);
        
            $alignmentPeriod = new Duration();
            $alignmentPeriod->setSeconds(600);
        
            $aggregation = new Aggregation();
            $aggregation->setAlignmentPeriod($alignmentPeriod);
            $aggregation->setGroupByFields(['metric.label."check_id"']);
            $aggregation->setCrossSeriesReducer(Reducer::REDUCE_SUM);
            $aggregation->setPerSeriesAligner(Aligner::ALIGN_COUNT_TRUE);
        
            $view = TimeSeriesView::FULL;
        
            // List all uptimecheck metrics and sum it by check_id
            // If a metric only have zeros
            $this->logger->info("Get aggregated uptimecheck metrics");
            $result = $metrics->listTimeSeries(
                $projectName,
                $filter,
                $interval,
                $view,
                ['aggregation' => $aggregation]);
        
        
            foreach ($result->iterateAllElements() as $timeSeries) {
                $checkId = $timeSeries->getMetric()->getLabels()["check_id"];
        
                $result = [];
                $maxCount = 0;
                foreach ($timeSeries->getPoints() as $point) {
                    $maxCount = max($maxCount, $point->getValue()->getInt64Value());
                    $result[] = $point->getValue()->getInt64Value();
                }
                if (!$maxCount) {
                    $this->logger->info("Found the following uptimeCheck without data since " . $minutesAgo . " minutes ago : " . $checkId);
                    try {
                        $uptimeCheckFullName = $this->getUptimeCheckName($projectId, $checkId);
                        $uptimeCheck = $uptimeCheckClient->getUptimeCheckConfig($uptimeCheckFullName);
                        $this->logger->info("UptimeCheck " . $checkId . " found with the following configuration : " . $uptimeCheck->serializeToJsonString());
                        $uptimeCheckClient->deleteUptimeCheckConfig($uptimeCheckFullName);
                        $this->logger->info("UptimeCheck " . $checkId . " deleted");
                    } catch (ApiException $ex) {
                        if ($ex->getStatus() === "NOT_FOUND") {
                            $this->logger->warning($ex->getBasicMessage());
                        } else {
                            $this->logger->error("Exception : (" . $ex->getStatus() . ") " . $ex->getBasicMessage());
                        }
                    }
                }
            }
        } catch (\Exception $ex) {
            return Command::FAILURE;
        } finally {
            $metrics->close();
        }

        return Command::SUCCESS;
    }

    private function getTimestamp(int $minutesAgo = 0) : Timestamp {
        $time = new Timestamp();
        $time->setSeconds(time() - (60 * $minutesAgo));
        return $time;
    }

    private function getTimeInterval(Timestamp $start, Timestamp $end) : TimeInterval {
        $interval = new TimeInterval();
        $interval->setStartTime($start);
        $interval->setEndTime($end);

        return $interval;
    }
}
