<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\Sql;

use Akeneo\Platform\Bundle\ImportExportBundle\Query\GetLastOperationsInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class GetLastOperationsIntegration extends TestCase
{
    /** @var JobLauncher */
    protected $jobLauncher;

    public function testGetLastOperationsWithPermissions(): void
    {
        $this->jobLauncher->launchExport('csv_product_export', 'julia');
        $this->jobLauncher->launchExport('csv_product_export', 'admin');
        $this->jobLauncher->launchExport('csv_product_model_export', 'julia');
        $this->jobLauncher->launchExport('csv_product_model_export', 'admin');

        $julia = $this->get('pim_user.repository.user')->findOneByIdentifier('julia');
        $this->revokeAccess($julia, 'csv_product_model_export');

        $lastOperations = $this->getLastOperationsQuery()->execute($julia);
        $this->assertCount(1, $lastOperations);

        foreach ($lastOperations as $lastOperation) {
            $this->assertCSVProductExportOperation($lastOperation);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = new JobLauncher(static::$kernel);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getLastOperationsQuery(): GetLastOperationsInterface
    {
        return $this->get('pim_import_export.query.get_last_operations');
    }

    private function assertCSVProductExportOperation(array $lastOperation): void
    {
        $this->assertCount(7, $lastOperation);

        $expectedKeys = ['id', 'date', 'job_instance_id', 'type', 'label', 'status', 'warningCount'];
        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $lastOperation);
        }

        $this->assertNotEmpty($lastOperation['id']);
        $this->assertNotEmpty($lastOperation['job_instance_id']);
        $this->assertNotEmpty($lastOperation['date']);
        $this->assertEquals('export', $lastOperation['type']);
        $this->assertEquals('CSV product export', $lastOperation['label']);
        $this->assertEquals(1, $lastOperation['status']);
        $this->assertEquals('0', $lastOperation['warningCount']);
    }

    private function revokeAccess(UserInterface $user, string $jobInstanceCode): void
    {
        $jobInstanceRepo = $this->get('pim_enrich.repository.job_instance');
        $jobProfileAccessRepo = $this->get('pimee_security.repository.job_profile_access');

        $jobInstance = $jobInstanceRepo->findOneBy(['code' => $jobInstanceCode]);

        $jobProfileAccessRepo->revokeAccess($jobInstance, $user->getGroups()->toArray());
    }
}
