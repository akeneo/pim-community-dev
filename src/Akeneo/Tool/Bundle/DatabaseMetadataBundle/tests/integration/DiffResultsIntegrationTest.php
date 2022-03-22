<?php

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\tests\integration;

use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Model\MysqlSyncReport;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Service\DiffResults;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\tests\Resources\IndexDatasResults;

use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class DiffResultsIntegrationTest extends KernelTestCase
{
    public function test_it_return_diff_db_less_data(): void
    {
        $oldResults = array();
        $newResults = array(
            "{\"identifier\":\"notice_absorb_notice_2_0d70d0e8-44f0-4d99-88a2-4472c8534a8b\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"notice_absorb_notice_3_9d884abe-dc33-4ddc-9d50-4e2997f5195f\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_1_f9b297a7-5072-48a7-889c-c06897eff369\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_2_a9cfb458-69a0-476a-bf76-4799f1f7a6ca\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $line = IndexDatasResults::initDiffBlock($oldResults, $newResults);
        $testEval = DiffResults::exploitDiffHelperResults($line);

        $identifier = ['notice_absorb_notice_2_0d70d0e8-44f0-4d99-88a2-4472c8534a8b',
            'notice_absorb_notice_3_9d884abe-dc33-4ddc-9d50-4e2997f5195f',
            'packshot_absorb_packshot_1_f9b297a7-5072-48a7-889c-c06897eff369',
            'packshot_absorb_packshot_2_a9cfb458-69a0-476a-bf76-4799f1f7a6ca'];
        $fixture = new MysqlSyncReport();
        $fixture->lines2Delete = $identifier;

        Assert::assertEquals($fixture, $testEval);
    }

    public function test_it_return_diff_db_more_data(): void
    {
        $oldResults = array(
            "{\"identifier\":\"packshot_absorb_packshot_3_5774db37-03d7-4396-9fad-b86ba7ab62e4\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_4_da78473b-2b40-4c81-9119-7a93bbc4600f\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $newResults = array();
        $line = IndexDatasResults::initDiffBlock($oldResults, $newResults);
        $testEval = DiffResults::exploitDiffHelperResults($line);

        $identifier = ['packshot_absorb_packshot_3_5774db37-03d7-4396-9fad-b86ba7ab62e4',
            'packshot_absorb_packshot_4_da78473b-2b40-4c81-9119-7a93bbc4600f'];
        $fixture = new MysqlSyncReport();
        $fixture->missingLines = $identifier;

        Assert::assertEquals($fixture, $testEval);
    }

    public function test_it_return_diff_db_updated_data(): void
    {
        $oldResults = array(
            "{\"identifier\":\"atmosphere_absorb_atmosphere_1_b238727b-1979-4559-9eec-8511281ee5f6\",\"date\":{\"date\":\"2022-03-03 08:58:50.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $newResults = array(
            "{\"identifier\":\"atmosphere_absorb_atmosphere_1_b238727b-1979-4559-9eec-8511281ee5f6\",\"date\":{\"date\":\"2022-03-03 07:58:50.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $line = IndexDatasResults::initDiffBlock($oldResults, $newResults);
        $testEval = DiffResults::exploitDiffHelperResults($line);

        $identifier = ['atmosphere_absorb_atmosphere_1_b238727b-1979-4559-9eec-8511281ee5f6'];
        $fixture = new MysqlSyncReport();
        $fixture->obsoleteLines = $identifier;

        Assert::assertEquals($fixture, $testEval);
    }

    public function test_it_return_diff_es_less_data(): void
    {
        $oldResults = array(
            "{\"identifier\":\"notice_absorb_notice_2_0d70d0e8-44f0-4d99-88a2-4472c8534a8b\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"notice_absorb_notice_3_9d884abe-dc33-4ddc-9d50-4e2997f5195f\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_1_f9b297a7-5072-48a7-889c-c06897eff369\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_2_a9cfb458-69a0-476a-bf76-4799f1f7a6ca\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $newResults = array();
        $line = IndexDatasResults::initDiffBlock($oldResults, $newResults);
        $testEval = DiffResults::exploitDiffHelperResults($line);

        $identifier = ['notice_absorb_notice_2_0d70d0e8-44f0-4d99-88a2-4472c8534a8b',
            'notice_absorb_notice_3_9d884abe-dc33-4ddc-9d50-4e2997f5195f',
            'packshot_absorb_packshot_1_f9b297a7-5072-48a7-889c-c06897eff369',
            'packshot_absorb_packshot_2_a9cfb458-69a0-476a-bf76-4799f1f7a6ca'];
        $fixture = new MysqlSyncReport();
        $fixture->missingLines = $identifier;

        Assert::assertEquals($fixture, $testEval);
    }

    public function test_it_return_diff_es_more_data(): void
    {
        $oldResults = array();
        $newResults = array(
            "{\"identifier\":\"packshot_absorb_packshot_3_5774db37-03d7-4396-9fad-b86ba7ab62e4\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_4_da78473b-2b40-4c81-9119-7a93bbc4600f\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $line = IndexDatasResults::initDiffBlock($oldResults, $newResults);
        $testEval = DiffResults::exploitDiffHelperResults($line);

        $identifier = ['packshot_absorb_packshot_3_5774db37-03d7-4396-9fad-b86ba7ab62e4',
            'packshot_absorb_packshot_4_da78473b-2b40-4c81-9119-7a93bbc4600f'];
        $fixture = new MysqlSyncReport();
        $fixture->lines2Delete = $identifier;

        Assert::assertEquals($fixture, $testEval);
    }

    public function test_it_return_diff_es_updated_data(): void
    {
        $oldResults = array(
            "{\"identifier\":\"atmosphere_admete_atmosphere_2_0f77baab-3287-4bfa-bd16-02421ae9a18a\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $newResults = array(
            "{\"identifier\":\"atmosphere_admete_atmosphere_2_0f77baab-3287-4bfa-bd16-02421ae9a18a\",\"date\":{\"date\":\"2022-03-03 08:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $line = IndexDatasResults::initDiffBlock($oldResults, $newResults);
        $testEval = DiffResults::exploitDiffHelperResults($line);

        $identifier = ['atmosphere_admete_atmosphere_2_0f77baab-3287-4bfa-bd16-02421ae9a18a'];
        $fixture = new MysqlSyncReport();
        $fixture->obsoleteLines = $identifier;

        Assert::assertEquals($fixture, $testEval);
    }

    public function test_it_return_no_diff_data(): void
    {
        $oldResults = array();
        $newResults = array();
        $line = IndexDatasResults::initDiffBlock($oldResults, $newResults);

        $testEval = DiffResults::exploitDiffHelperResults($line);

        $fixture = new MysqlSyncReport();
        Assert::assertEquals($fixture, $testEval);
    }

    public function test_it_return_diff_db_more_data_and_updated(): void
    {
        $oldResults = array(
            "{\"identifier\":\"atmosphere_admete_atmosphere_2_0f77baab-3287-4bfa-bd16-02421ae9a18a\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_3_5774db37-03d7-4396-9fad-b86ba7ab62e4\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_4_da78473b-2b40-4c81-9119-7a93bbc4600f\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $newResults = array(
            "{\"identifier\":\"atmosphere_admete_atmosphere_2_0f77baab-3287-4bfa-bd16-02421ae9a18a\",\"date\":{\"date\":\"2022-03-03 08:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $line = IndexDatasResults::initDiffBlock($oldResults, $newResults);
        $testEval = DiffResults::exploitDiffHelperResults($line);

        $identifier0bsolete = [
            'atmosphere_admete_atmosphere_2_0f77baab-3287-4bfa-bd16-02421ae9a18a',
        ];
        $identifierMissing = [
            1 => 'packshot_absorb_packshot_3_5774db37-03d7-4396-9fad-b86ba7ab62e4',
            2 => 'packshot_absorb_packshot_4_da78473b-2b40-4c81-9119-7a93bbc4600f'
        ];

        $fixture = new MysqlSyncReport();
        $fixture->missingLines = $identifierMissing;
        $fixture->obsoleteLines = $identifier0bsolete;

        Assert::assertEquals($fixture, $testEval);
    }

    public function test_it_return_diff_db_less_data_and_updated(): void
    {
        $oldResults = array(
            "{\"identifier\":\"atmosphere_admete_atmosphere_2_0f77baab-3287-4bfa-bd16-02421ae9a18a\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $newResults = array(
            "{\"identifier\":\"atmosphere_admete_atmosphere_2_0f77baab-3287-4bfa-bd16-02421ae9a18a\",\"date\":{\"date\":\"2022-03-03 08:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"notice_absorb_notice_2_0d70d0e8-44f0-4d99-88a2-4472c8534a8b\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"notice_absorb_notice_3_9d884abe-dc33-4ddc-9d50-4e2997f5195f\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_1_f9b297a7-5072-48a7-889c-c06897eff369\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_2_a9cfb458-69a0-476a-bf76-4799f1f7a6ca\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $line = IndexDatasResults::initDiffBlock($oldResults, $newResults);
        $testEval = DiffResults::exploitDiffHelperResults($line);

        $identifier0bsolete = [
            'atmosphere_admete_atmosphere_2_0f77baab-3287-4bfa-bd16-02421ae9a18a',
        ];
        $identifierDelete = [
            1 => 'notice_absorb_notice_2_0d70d0e8-44f0-4d99-88a2-4472c8534a8b',
            2 => 'notice_absorb_notice_3_9d884abe-dc33-4ddc-9d50-4e2997f5195f',
            3 => 'packshot_absorb_packshot_1_f9b297a7-5072-48a7-889c-c06897eff369',
            4 => 'packshot_absorb_packshot_2_a9cfb458-69a0-476a-bf76-4799f1f7a6ca'
        ];
        $fixture = new MysqlSyncReport();
        $fixture->lines2Delete = $identifierDelete;
        $fixture->obsoleteLines = $identifier0bsolete;

        Assert::assertEquals($fixture, $testEval);
    }

    public function test_it_return_diff_es_more_data_and_updated(): void
    {
        $oldResults = array(
            "{\"identifier\":\"atmosphere_absorb_atmosphere_1_b238727b-1979-4559-9eec-8511281ee5f6\",\"date\":{\"date\":\"2022-03-03 08:58:50.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $newResults = array(
            "{\"identifier\":\"atmosphere_absorb_atmosphere_1_b238727b-1979-4559-9eec-8511281ee5f6\",\"date\":{\"date\":\"2022-03-03 07:58:50.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_3_5774db37-03d7-4396-9fad-b86ba7ab62e4\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_4_da78473b-2b40-4c81-9119-7a93bbc4600f\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $line = IndexDatasResults::initDiffBlock($oldResults, $newResults);
        $testEval = DiffResults::exploitDiffHelperResults($line);

        $identifier0bsolete = [
            'atmosphere_absorb_atmosphere_1_b238727b-1979-4559-9eec-8511281ee5f6',
        ];
        $identifierDelete = [
            1 => 'packshot_absorb_packshot_3_5774db37-03d7-4396-9fad-b86ba7ab62e4',
            2 => 'packshot_absorb_packshot_4_da78473b-2b40-4c81-9119-7a93bbc4600f'
        ];
        $fixture = new MysqlSyncReport();
        $fixture->lines2Delete = $identifierDelete;
        $fixture->obsoleteLines = $identifier0bsolete;

        Assert::assertEquals($fixture, $testEval);
    }

    public function test_it_return_diff_es_less_data_and_updated(): void
    {
        $oldResults = array(
            "{\"identifier\":\"atmosphere_absorb_atmosphere_1_b238727b-1979-4559-9eec-8511281ee5f6\",\"date\":{\"date\":\"2022-03-03 08:58:50.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"notice_absorb_notice_2_0d70d0e8-44f0-4d99-88a2-4472c8534a8b\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"notice_absorb_notice_3_9d884abe-dc33-4ddc-9d50-4e2997f5195f\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_1_f9b297a7-5072-48a7-889c-c06897eff369\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_2_a9cfb458-69a0-476a-bf76-4799f1f7a6ca\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $newResults = array(
            "{\"identifier\":\"atmosphere_absorb_atmosphere_1_b238727b-1979-4559-9eec-8511281ee5f6\",\"date\":{\"date\":\"2022-03-03 07:58:50.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $line = IndexDatasResults::initDiffBlock($oldResults, $newResults);
        $testEval = DiffResults::exploitDiffHelperResults($line);

        $fixture = new MysqlSyncReport();
        $identifier0bsolete = [
            'atmosphere_absorb_atmosphere_1_b238727b-1979-4559-9eec-8511281ee5f6',
        ];
        $identifierMissing = [
            1 => 'notice_absorb_notice_2_0d70d0e8-44f0-4d99-88a2-4472c8534a8b',
            2 => 'notice_absorb_notice_3_9d884abe-dc33-4ddc-9d50-4e2997f5195f',
            3 => 'packshot_absorb_packshot_1_f9b297a7-5072-48a7-889c-c06897eff369',
            4 => 'packshot_absorb_packshot_2_a9cfb458-69a0-476a-bf76-4799f1f7a6ca'
        ];
        $fixture->missingLines = $identifierMissing;
        $fixture->obsoleteLines = $identifier0bsolete;

        Assert::assertEquals($fixture, $testEval);
    }

    public function test_it_return_diff_es_updated_and_db_updated(): void
    {
        $oldResults = array(
            "{\"identifier\":\"atmosphere_absorb_atmosphere_1_b238727b-1979-4559-9eec-8511281ee5f6\",\"date\":{\"date\":\"2022-03-03 08:58:50.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"atmosphere_admete_atmosphere_2_0f77baab-3287-4bfa-bd16-02421ae9a18a\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $newResults = array(
            "{\"identifier\":\"atmosphere_absorb_atmosphere_1_b238727b-1979-4559-9eec-8511281ee5f6\",\"date\":{\"date\":\"2022-03-03 07:58:50.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"atmosphere_admete_atmosphere_2_0f77baab-3287-4bfa-bd16-02421ae9a18a\",\"date\":{\"date\":\"2022-03-03 08:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $line = IndexDatasResults::initDiffBlock($oldResults, $newResults);
        $testEval = DiffResults::exploitDiffHelperResults($line);

        $identifier = [
            'atmosphere_absorb_atmosphere_1_b238727b-1979-4559-9eec-8511281ee5f6',
            'atmosphere_admete_atmosphere_2_0f77baab-3287-4bfa-bd16-02421ae9a18a'
        ];
        $fixture = new MysqlSyncReport();
        $fixture->obsoleteLines = $identifier;

        Assert::assertEquals($fixture, $testEval);
    }

    public function test_it_return_diff_es_less_and_db_more(): void
    {
        $oldResults = array(
            "{\"identifier\":\"notice_absorb_notice_2_0d70d0e8-44f0-4d99-88a2-4472c8534a8b\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"notice_absorb_notice_3_9d884abe-dc33-4ddc-9d50-4e2997f5195f\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_1_f9b297a7-5072-48a7-889c-c06897eff369\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_2_a9cfb458-69a0-476a-bf76-4799f1f7a6ca\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_3_5774db37-03d7-4396-9fad-b86ba7ab62e4\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_4_da78473b-2b40-4c81-9119-7a93bbc4600f\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $newResults = array();
        $line = IndexDatasResults::initDiffBlock($oldResults, $newResults);
        $testEval = DiffResults::exploitDiffHelperResults($line);

        $identifier = [
            'notice_absorb_notice_2_0d70d0e8-44f0-4d99-88a2-4472c8534a8b',
            'notice_absorb_notice_3_9d884abe-dc33-4ddc-9d50-4e2997f5195f',
            'packshot_absorb_packshot_1_f9b297a7-5072-48a7-889c-c06897eff369',
            'packshot_absorb_packshot_2_a9cfb458-69a0-476a-bf76-4799f1f7a6ca',
            'packshot_absorb_packshot_3_5774db37-03d7-4396-9fad-b86ba7ab62e4',
            'packshot_absorb_packshot_4_da78473b-2b40-4c81-9119-7a93bbc4600f'
        ];
        $fixture = new MysqlSyncReport();
        $fixture->missingLines = $identifier;

        Assert::assertEquals($fixture, $testEval);
    }

    public function test_it_return_diff_es_more_and_db_less(): void
    {
        $oldResults = array();
        $newResults = array(
            "{\"identifier\":\"notice_absorb_notice_2_0d70d0e8-44f0-4d99-88a2-4472c8534a8b\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"notice_absorb_notice_3_9d884abe-dc33-4ddc-9d50-4e2997f5195f\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_1_f9b297a7-5072-48a7-889c-c06897eff369\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_2_a9cfb458-69a0-476a-bf76-4799f1f7a6ca\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_3_5774db37-03d7-4396-9fad-b86ba7ab62e4\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_4_da78473b-2b40-4c81-9119-7a93bbc4600f\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $line = IndexDatasResults::initDiffBlock($oldResults, $newResults);
        $testEval = DiffResults::exploitDiffHelperResults($line);

        $identifier = [
            'notice_absorb_notice_2_0d70d0e8-44f0-4d99-88a2-4472c8534a8b',
            'notice_absorb_notice_3_9d884abe-dc33-4ddc-9d50-4e2997f5195f',
            'packshot_absorb_packshot_1_f9b297a7-5072-48a7-889c-c06897eff369',
            'packshot_absorb_packshot_2_a9cfb458-69a0-476a-bf76-4799f1f7a6ca',
            'packshot_absorb_packshot_3_5774db37-03d7-4396-9fad-b86ba7ab62e4',
            'packshot_absorb_packshot_4_da78473b-2b40-4c81-9119-7a93bbc4600f'
        ];
        $fixture = new MysqlSyncReport();
        $fixture->lines2Delete = $identifier;

        Assert::assertEquals($fixture, $testEval);
    }

    public function test_it_return_diff_es_more_and_db_more_diff(): void
    {
        $oldResults = array(
            "{\"identifier\":\"user_guide_1_4_administrator_df67a383-fad1-4ca0-b818-b2ff3d49ffa4\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"user_guide_1_4_catalog_setting_d50fd111-4abb-4ede-8767-10d8dfa969fc\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"user_guide_1_4_end_user_role_ea9c1630-6296-4753-9cd7-16ccf1eb51a3\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"user_guide_1_5_administrator_0eb69e4a-a8d0-43cc-8783-dedb4a94d357\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $newResults = array(
            "{\"identifier\":\"packshot_absorb_packshot_1_f9b297a7-5072-48a7-889c-c06897eff369\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_2_a9cfb458-69a0-476a-bf76-4799f1f7a6ca\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_3_5774db37-03d7-4396-9fad-b86ba7ab62e4\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_4_da78473b-2b40-4c81-9119-7a93bbc4600f\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $line = IndexDatasResults::initDiffBlock($oldResults, $newResults);
        $testEval = DiffResults::exploitDiffHelperResults($line);

        $identifierMissing = [
            'user_guide_1_4_administrator_df67a383-fad1-4ca0-b818-b2ff3d49ffa4',
            'user_guide_1_4_catalog_setting_d50fd111-4abb-4ede-8767-10d8dfa969fc',
            'user_guide_1_4_end_user_role_ea9c1630-6296-4753-9cd7-16ccf1eb51a3',
            'user_guide_1_5_administrator_0eb69e4a-a8d0-43cc-8783-dedb4a94d357'
        ];
        $identifierDelete = [
            'packshot_absorb_packshot_1_f9b297a7-5072-48a7-889c-c06897eff369',
            'packshot_absorb_packshot_2_a9cfb458-69a0-476a-bf76-4799f1f7a6ca',
            'packshot_absorb_packshot_3_5774db37-03d7-4396-9fad-b86ba7ab62e4',
            'packshot_absorb_packshot_4_da78473b-2b40-4c81-9119-7a93bbc4600f'
        ];

        $fixture = new MysqlSyncReport();
        $fixture->missingLines = $identifierMissing;
        $fixture->lines2Delete = $identifierDelete;

        Assert::assertEquals($fixture, $testEval);
    }

    public function test_it_return_diff_es_more_and_db_more_and_updated_diff(): void
    {
        $oldResults = array(
            "{\"identifier\":\"atmosphere_absorb_atmosphere_2_85f4701c-147b-470b-8733-f532ae318d5b\",\"date\":{\"date\":\"2022-03-03 08:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"notice_absorb_notice_3_9d884abe-dc33-4ddc-9d50-4e2997f5195f\",\"date\":{\"date\":\"2022-03-03 08:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"user_guide_1_4_administrator_df67a383-fad1-4ca0-b818-b2ff3d49ffa4\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"user_guide_1_4_catalog_setting_d50fd111-4abb-4ede-8767-10d8dfa969fc\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"user_guide_1_4_end_user_role_ea9c1630-6296-4753-9cd7-16ccf1eb51a3\",\"date\":{\"date\":\"2022-03-03 08:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"user_guide_1_5_administrator_0eb69e4a-a8d0-43cc-8783-dedb4a94d357\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $newResults = array(
            "{\"identifier\":\"atmosphere_absorb_atmosphere_2_85f4701c-147b-470b-8733-f532ae318d5b\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"notice_absorb_notice_3_9d884abe-dc33-4ddc-9d50-4e2997f5195f\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_1_f9b297a7-5072-48a7-889c-c06897eff369\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_2_a9cfb458-69a0-476a-bf76-4799f1f7a6ca\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_3_5774db37-03d7-4396-9fad-b86ba7ab62e4\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_4_da78473b-2b40-4c81-9119-7a93bbc4600f\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $line = IndexDatasResults::initDiffBlock($oldResults, $newResults);
        $testEval = DiffResults::exploitDiffHelperResults($line);

        $identifierMissing = [
            2 => 'user_guide_1_4_administrator_df67a383-fad1-4ca0-b818-b2ff3d49ffa4',
            3 => 'user_guide_1_4_catalog_setting_d50fd111-4abb-4ede-8767-10d8dfa969fc',
            4 => 'user_guide_1_4_end_user_role_ea9c1630-6296-4753-9cd7-16ccf1eb51a3',
            5 => 'user_guide_1_5_administrator_0eb69e4a-a8d0-43cc-8783-dedb4a94d357'
        ];
        $identifierDelete = [
            2 => 'packshot_absorb_packshot_1_f9b297a7-5072-48a7-889c-c06897eff369',
            3 => 'packshot_absorb_packshot_2_a9cfb458-69a0-476a-bf76-4799f1f7a6ca',
            4 => 'packshot_absorb_packshot_3_5774db37-03d7-4396-9fad-b86ba7ab62e4',
            5 => 'packshot_absorb_packshot_4_da78473b-2b40-4c81-9119-7a93bbc4600f'
        ];
        $identifierObsolete = [
            0 => 'atmosphere_absorb_atmosphere_2_85f4701c-147b-470b-8733-f532ae318d5b',
            1 => 'notice_absorb_notice_3_9d884abe-dc33-4ddc-9d50-4e2997f5195f'
        ];
        $fixture = new MysqlSyncReport();
        $fixture->lines2Delete = $identifierDelete;
        $fixture->missingLines = $identifierMissing;
        $fixture->obsoleteLines = $identifierObsolete;

        Assert::assertEquals($fixture, $testEval);
    }

    public function test_it_return_diff_es_and_db_all_updated(): void
    {
        $oldResults = array(
            "{\"identifier\":\"atmosphere_absorb_atmosphere_1_b238727b-1979-4559-9eec-8511281ee5f6\",\"date\":{\"date\":\"2022-03-03 07:58:50.000000\",\"timezone_type\":3,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"atmosphere_absorb_atmosphere_2_85f4701c-147b-470b-8733-f532ae318d5b\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":3,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"atmosphere_admete_atmosphere_1_b73a5053-c45c-4e65-acdc-683c96e58aa8\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":3,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"atmosphere_admete_atmosphere_2_0f77baab-3287-4bfa-bd16-02421ae9a18a\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":3,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"notice_absorb_notice_2_0d70d0e8-44f0-4d99-88a2-4472c8534a8b\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":3,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"notice_absorb_notice_3_9d884abe-dc33-4ddc-9d50-4e2997f5195f\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":3,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_1_f9b297a7-5072-48a7-889c-c06897eff369\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":3,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_2_a9cfb458-69a0-476a-bf76-4799f1f7a6ca\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":3,\"timezone\":\"+00:00\"}}"
        );
        $newResults = array(
            "{\"identifier\":\"atmosphere_absorb_atmosphere_1_b238727b-1979-4559-9eec-8511281ee5f6\",\"date\":{\"date\":\"2022-03-03 07:58:50.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"atmosphere_absorb_atmosphere_2_85f4701c-147b-470b-8733-f532ae318d5b\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"atmosphere_admete_atmosphere_1_b73a5053-c45c-4e65-acdc-683c96e58aa8\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"atmosphere_admete_atmosphere_2_0f77baab-3287-4bfa-bd16-02421ae9a18a\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"notice_absorb_notice_2_0d70d0e8-44f0-4d99-88a2-4472c8534a8b\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"notice_absorb_notice_3_9d884abe-dc33-4ddc-9d50-4e2997f5195f\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_1_f9b297a7-5072-48a7-889c-c06897eff369\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}",
            "{\"identifier\":\"packshot_absorb_packshot_2_a9cfb458-69a0-476a-bf76-4799f1f7a6ca\",\"date\":{\"date\":\"2022-03-03 07:58:51.000000\",\"timezone_type\":1,\"timezone\":\"+00:00\"}}"
        );
        $line = IndexDatasResults::initDiffBlock($oldResults, $newResults);
        $testEval = DiffResults::exploitDiffHelperResults($line);

        $identifier = [
            'atmosphere_absorb_atmosphere_1_b238727b-1979-4559-9eec-8511281ee5f6',
            'atmosphere_absorb_atmosphere_2_85f4701c-147b-470b-8733-f532ae318d5b',
            'atmosphere_admete_atmosphere_1_b73a5053-c45c-4e65-acdc-683c96e58aa8',
            'atmosphere_admete_atmosphere_2_0f77baab-3287-4bfa-bd16-02421ae9a18a',
            'notice_absorb_notice_2_0d70d0e8-44f0-4d99-88a2-4472c8534a8b',
            'notice_absorb_notice_3_9d884abe-dc33-4ddc-9d50-4e2997f5195f',
            'packshot_absorb_packshot_1_f9b297a7-5072-48a7-889c-c06897eff369',
            'packshot_absorb_packshot_2_a9cfb458-69a0-476a-bf76-4799f1f7a6ca'
        ];
        $fixture = new MysqlSyncReport();
        $fixture->obsoleteLines = $identifier;

        Assert::assertEquals($fixture, $testEval);
    }
}
