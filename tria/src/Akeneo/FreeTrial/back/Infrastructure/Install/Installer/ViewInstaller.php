<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\Install\Installer;

use Akeneo\FreeTrial\Infrastructure\Install\InstallCatalogTrait;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

final class ViewInstaller implements FixtureInstaller
{
    use InstallCatalogTrait;

    private Connection $dbConnection;

    private LoggerInterface $logger;

    public function __construct(Connection $dbConnection, LoggerInterface $logger)
    {
        $this->dbConnection = $dbConnection;
        $this->logger = $logger;
    }

    public function install(): void
    {
        $this->ensureFirstUserExists();

        $query = <<<SQL
INSERT INTO pim_datagrid_view (owner_id, label, type, datagrid_alias, columns, filters) 
VALUES (1, :label, 'public', :datagrid_alias, :columns, :filters)
SQL;

        foreach ($this->getViewsData() as $viewData) {
            $this->dbConnection->executeQuery($query, $viewData);
        }
    }

    private function getViewsData(): \Iterator
    {
        $sourceFile = fopen($this->getViewFixturesPath(), 'r');
        if (false === $sourceFile) {
            throw new \Exception(sprintf('Failed to open views fixtures file "%s"', $this->getViewFixturesPath()));
        }

        $header = fgetcsv($sourceFile, 0, "\t");
        if ('label' !== $header[0]) {
            throw new \Exception('Invalid CSV views header');
        }

        $categoriesCodes = $this->retrieveCategoriesCodes();
        if (empty($categoriesCodes)) {
            return;
        }

        while ($row = fgetcsv($sourceFile, 0, "\t")) {
            $viewData = array_combine($header, $row);
            try {
                $viewData = $this->replaceCategoriesIds($viewData, $categoriesCodes);
            } catch (\Exception $exception) {
                $this->logger->error(
                    sprintf('Failed to install view "%s"', $viewData['label'] ?? ''),
                    ['message' => $exception->getMessage()]
                );
                continue;
            }

            yield $viewData;
        }
    }

    private function retrieveCategoriesCodes(): array
    {
        $sourceFile = @fopen($this->getCategoryCodeFixturesPath(), 'r');
        if (false === $sourceFile) {
            throw new \Exception(sprintf('Failed to open categories codes file "%s"', $this->getCategoryCodeFixturesPath()));
        }

        $categoriesCodes = [];
        while ($row = fgetcsv($sourceFile, 0, "\t")) {
            $categoriesCodes[$row[0]] = $row[1] ?? '';
        }

        return $categoriesCodes;
    }

    private function replaceCategoriesIds(array $viewData, array $categoriesCodes): array
    {
        $filters = [];
        parse_str($viewData['filters'], $filters);

        if (!isset($filters['f']['category']['value'])) {
            return $viewData;
        }

        $filters['f']['category']['value']['treeId'] = $this->retrieveRealCategoryId($filters['f']['category']['value']['treeId'], $categoriesCodes);
        $filters['f']['category']['value']['categoryId'] = $this->retrieveRealCategoryId($filters['f']['category']['value']['categoryId'], $categoriesCodes);

        $viewData['filters'] = http_build_query($filters);

        return $viewData;
    }

    private function retrieveRealCategoryId(string $filterCategoryId, array $categoriesCodes): string
    {
        if (!isset($categoriesCodes[$filterCategoryId])) {
            throw new \Exception(sprintf('No code found for category "%s"', $filterCategoryId));
        }

        $query = <<<SQL
SELECT id FROM pim_catalog_category WHERE code = :code;
SQL;

        $realCategoryId = $this->dbConnection->executeQuery($query, ['code' => $categoriesCodes[$filterCategoryId]])->fetchColumn();
        if (!$realCategoryId) {
            throw new \Exception(sprintf('No id found for category "%s"', $categoriesCodes[$filterCategoryId]));
        }

        return $realCategoryId;
    }

    private function ensureFirstUserExists(): void
    {
        $query = <<<SQL
SELECT 1 FROM oro_user WHERE id = 1
SQL;

        $userExists = $this->dbConnection->executeQuery($query)->fetchColumn();

        if (false === boolval($userExists)) {
            throw new \Exception('Installing views needs at least one user');
        }
    }
}
