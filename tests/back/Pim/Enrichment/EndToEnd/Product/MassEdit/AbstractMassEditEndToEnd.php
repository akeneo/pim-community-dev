<?php
declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\MassEdit;

use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountInTransportTrait;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use AkeneoTest\Pim\Enrichment\EndToEnd\InternalApiTestCase;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractMassEditEndToEnd extends InternalApiTestCase
{
    use AssertEventCountInTransportTrait;

    protected JobLauncher $jobLauncher;
    protected Connection $dbalConnection;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        $this->dbalConnection = $this->get('database_connection');
        $this->authenticate($this->getAdminUser());
        $this->clearMessengerTransport();
    }

    protected function executeMassEdit(array $data): void
    {
        $this->client->request(
            'POST',
            '/rest/mass_edit/',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            json_encode($data)
        );
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $this->launchAndWaitForJob($data['jobInstanceCode']);
    }

    protected function launchAndWaitForJob(string $jobInstanceCode): void
    {
        $this->jobLauncher->launchConsumerOnce();

        $query = <<<SQL
SELECT exec.status, exec.id
FROM akeneo_batch_job_execution as exec
INNER JOIN akeneo_batch_job_instance as instance ON exec.job_instance_id = instance.id AND instance.code = :instance_code   
ORDER BY exec.id DESC
LIMIT 1;
SQL;
        $timeout = 0;
        $isCompleted = false;

        $stmt = $this->dbalConnection->prepare($query);

        while (!$isCompleted) {
            if ($timeout > 30) {
                throw new \RuntimeException(
                    sprintf(
                        'Timeout: last job execution from "%s" job instance is not complete.',
                        $jobInstanceCode
                    )
                );
            }
            $stmt->bindValue('instance_code', $jobInstanceCode);
            $stmt->execute();
            $result = $stmt->fetch();

            $isCompleted = isset($result['status']) && BatchStatus::COMPLETED === (int) $result['status'];

            $timeout++;

            sleep(1);
        }
    }

    protected function findESIdFor(string $identifier, string $type): string
    {
        switch ($type) {
            case 'product':
                $idFromDatabase = $this->getProductId($identifier);
                break;
            case 'product_model':
                $idFromDatabase = $this->getProductModelId($identifier);
                break;
            default:
                throw new \InvalidArgumentException(
                    'Only "product" or "product_model" types are allowed. Product variant is a product.'
                );
        }

        return sprintf('%s_%s', $type, $idFromDatabase);
    }

    protected function getProductId(string $identifier): string
    {
            $query = <<<SQL
SELECT id
FROM pim_catalog_product
WHERE identifier = :identifier
SQL;

        $stmt = $this->dbalConnection->prepare($query);
        $stmt->bindValue('identifier', $identifier);
        $stmt->execute();
        $idFromDatabase = $stmt->fetchColumn();

        if (false === $idFromDatabase) {
            throw new \InvalidArgumentException(sprintf(
                'Product with identifier "%s" does not exist.',
                $identifier
            ));
        }

        return $idFromDatabase;
    }

    protected function getProductModelId(string $code): string
    {
        $query = <<<SQL
SELECT id
FROM pim_catalog_product_model
WHERE code = :code
SQL;

        $stmt = $this->dbalConnection->prepare($query);
        $stmt->bindValue('code', $code);
        $stmt->execute();
        $idFromDatabase = $stmt->fetchColumn();

        if (false === $idFromDatabase) {
            throw new \InvalidArgumentException(sprintf(
                'Product model with code "%s" does not exist.',
                $code
            ));
        }

        return $idFromDatabase;
    }

    protected function updateProductWithInternalApi(string $identifier, array $data): Response
    {
        $this->client->request(
            'POST',
            sprintf('/enrich/product/rest/%s', $this->getProductId($identifier)),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            json_encode($data)
        );

        return $this->client->getResponse();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    protected function getAdminUser(): UserInterface
    {
        return self::$container->get('pim_user.repository.user')->findOneByIdentifier('admin');
    }
}
