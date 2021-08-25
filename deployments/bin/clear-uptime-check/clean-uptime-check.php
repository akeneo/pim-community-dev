<?php
require __DIR__ . '/vendor/autoload.php';

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
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


$logLevel = getenv('LOG_LEVEL') ?: 'WARNING';
$projectId=getenv('PROJECT') ?: 'akecld-saas-dev';

$logger = new Logger('clean-uptime-check');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::toMonologLevel($logLevel)));

$metrics = new MetricServiceClient(['projectId' => $projectId]);
$projectName = $metrics->projectName($projectId);

$uptimeCheckClient = new UptimeCheckServiceClient(['projectId' => $projectId]);
$uptimeCheckClient->projectName($projectId);

try {
    $filter = 'metric.type="monitoring.googleapis.com/uptime_check/check_passed" resource.type="uptime_url" metric.label."check_id"=monitoring.regex.full_match("pim(ci|up)-.*")';

    $minutesAgo = 60;
    $startTime = new Timestamp();
    $startTime->setSeconds(time() - (60 * $minutesAgo));
    $endTime = new Timestamp();
    $endTime->setSeconds(time());

    $interval = new TimeInterval();
    $interval->setStartTime($startTime);
    $interval->setEndTime($endTime);

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
    $logger->info("Get aggreagted uptimecheck metrics");
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
            $logger->info("Found the following uptimeCheck without data since " . $minutesAgo . " minutes ago : " . $checkId);
            try {
                $uptimeCheckFullName = getUptimeCheckName($projectId, $checkId);
                $uptimeCheck = $uptimeCheckClient->getUptimeCheckConfig($uptimeCheckFullName);
                $logger->info("UptimeCheck " . $checkId . " found with the following configuration : " . $uptimeCheck->serializeToJsonString());
                $uptimeCheckClient->deleteUptimeCheckConfig($uptimeCheckFullName);
                $logger->info("UptimeCheck " . $checkId . " deleted");
            } catch (ApiException $ex) {
                if ($ex->getStatus() === "NOT_FOUND") {
                    $logger->warning($ex->getBasicMessage());
                } else {
                    $logger->error("Exception : (" . $ex->getStatus() . ") " . $ex->getBasicMessage());
                }
            }
        }
    }
} finally {
    $metrics->close();
}

function getUptimeCheckName($projectId, $uptimeCheckId) {
    return "projects/" . $projectId . "/uptimeCheckConfigs/" . $uptimeCheckId;
}
