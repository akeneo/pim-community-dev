<?php

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Service;

use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Model\MysqlSyncReport;

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class DiffResults
{
    public static function exploitDiffHelperResults(string $dataResultsFromDiffHelper): MysqlSyncReport
    {
        $mysqlSyncReport = new MysqlSyncReport();
        $resultsDiffHelper = json_decode($dataResultsFromDiffHelper, true)?:[];
        $mySqlArrayIdentifier = [];
        $esArrayIdentifier=[];
        foreach ($resultsDiffHelper as $arraysFromDiffHelper) {
            foreach ($arraysFromDiffHelper as $pairArrayOldNew) {
                foreach ($pairArrayOldNew['old']['lines'] as $mySqlData) {
                    $mysqlArray = json_decode($mySqlData, true);
                    $mySqlArrayIdentifier[$mysqlArray["identifier"]] = $mysqlArray;
                }
                foreach ($pairArrayOldNew['new']['lines'] as $esData) {
                    $esArray = json_decode($esData, true);
                    if (!$esArray) {
                        continue;
                    }
                    $esArrayIdentifier[$esArray["identifier"]] = $esArray;
                }
            }
        }

        $mysqlSyncReport->missingLines = array_diff(
            array_keys($mySqlArrayIdentifier),
            array_keys($esArrayIdentifier)
        );
        $mysqlSyncReport->lines2Delete = array_diff(
            array_keys($esArrayIdentifier),
            array_keys($mySqlArrayIdentifier)
        );
        $mysqlSyncReport->obsoleteLines = array_diff(
            array_keys($mySqlArrayIdentifier),
            array_diff(
                array_keys($mySqlArrayIdentifier),
                array_keys($esArrayIdentifier)
            )
        );

        return $mysqlSyncReport;
    }
}
