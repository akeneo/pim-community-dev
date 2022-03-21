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
    public static function exploitDiffHelperResults(string $datasResults): MysqlSyncReport
    {
        $mysqlSyncReport = new MysqlSyncReport();
        $resultsDiffHelper = json_decode($datasResults, true)?:[];
        $mySqlArrayIdentifier = [];
        $esArrayIdentifier=[];
        foreach ($resultsDiffHelper as $arraysFromDiffHelper) {
            foreach ($arraysFromDiffHelper as $pairArrayOldNew) {
                foreach($pairArrayOldNew['old']['lines'] as $mySqlData){
                    $mysqlArray = json_decode($mySqlData, true);
                    $mySqlArrayIdentifier[$mysqlArray["identifier"]] = $mysqlArray;
                }
                foreach($pairArrayOldNew['new']['lines'] as $esData){
                    $esArray = json_decode($esData, true);
                    if(!$esArray){
                        continue;
                    }
                    $esArrayIdentifier[$esArray["identifier"]] = $esArray;
                }
            }
        }

        $mySQLOnly = array_diff(
            array_keys($mySqlArrayIdentifier),
            array_keys($esArrayIdentifier));
        $esOnly = array_diff(
            array_keys($esArrayIdentifier),
            array_keys($mySqlArrayIdentifier));
        $updated = array_diff(
            array_keys($mySqlArrayIdentifier),
            $mySQLOnly);
        $mysqlSyncReport->missingLines = $mySQLOnly;
        $mysqlSyncReport->lines2Delete = $esOnly;
        $mysqlSyncReport->obsoleteLines = $updated;

        return $mysqlSyncReport;
    }
}