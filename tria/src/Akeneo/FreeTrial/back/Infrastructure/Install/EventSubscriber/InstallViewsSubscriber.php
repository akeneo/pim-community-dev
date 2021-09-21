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

namespace Akeneo\FreeTrial\Infrastructure\Install\EventSubscriber;

use Akeneo\FreeTrial\Infrastructure\Install\InstallCatalogTrait;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;

final class InstallViewsSubscriber implements EventSubscriberInterface
{
    use InstallCatalogTrait;

    private const INSTALL_ERROR_LOG = 'An error occurred during Free-Trial views installation';

    private Connection $dbConnection;

    private LoggerInterface $logger;

    public function __construct(Connection $dbConnection, LoggerInterface $logger)
    {
        $this->dbConnection = $dbConnection;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'installViews',
        ];
    }

    public function installViews(AuthenticationSuccessEvent $event): void
    {
        $this->logger->debug('AUTHENTICATION_SUCCESS');

        $user = $event->getAuthenticationToken()->getUser();
        if (!$user instanceof UserInterface) {
            return;
        }

        if ($user->getId() !== 1 || $user->getLoginCount() > 1) {
            return;
        }

        if ($this->viewsAlreadyInstalled()) {
            $this->logger->debug('Free-Trial views already installed');
            return;
        }

        $this->logger->debug('Install views');

        $query = <<<SQL
INSERT INTO pim_datagrid_view (owner_id, label, type, datagrid_alias, columns, filters) 
VALUES (1, :label, 'public', :datagrid_alias, :columns, :filters)
SQL;

        foreach ($this->getViewsData() as $viewData) {
            try {
                $this->dbConnection->executeQuery($query, $viewData);
            } catch (\Throwable $exception) {
                $this->logger->error(self::INSTALL_ERROR_LOG, ['error_message' => $exception->getMessage()]);
            }
        }
    }

    private function viewsAlreadyInstalled(): bool
    {
        $query = <<<SQL
SELECT EXISTS(SELECT 1 FROM pim_datagrid_view);
SQL;

        return boolval($this->dbConnection->executeQuery($query)->fetchColumn());
    }

    private function getViewsData(): \Iterator
    {
        $sourceFile = @fopen($this->getViewsFixturesPath(), 'r');
        if (false === $sourceFile) {
            $this->logger->error(self::INSTALL_ERROR_LOG, ['message' => sprintf('Failed to open views fixtures file "%s"', $this->getViewsFixturesPath())]);
            return;
        }

        $header = fgetcsv($sourceFile, 0, "\t");
        if ('label' !== $header[0]) {
             $this->logger->error(self::INSTALL_ERROR_LOG, ['message' => 'Invalid CSV views header', 'header' => $header]);
             return;
        }

        $categoriesCodes = $this->retrieveCategoriesCodes();
        if (empty($categoriesCodes)) {
            return;
        }

        while ($row = fgetcsv($sourceFile, 0, "\t")) {
            $viewData = array_combine($header, $row);
            $viewData = $this->replaceCategoriesIds($viewData, $categoriesCodes);
            yield $viewData;
        }
    }

    private function retrieveCategoriesCodes(): array
    {
        $sourceFile = @fopen($this->getCategoriesCodesFixturesPath(), 'r');
        if (false === $sourceFile) {
            $this->logger->error(self::INSTALL_ERROR_LOG, ['message' => sprintf('Failed to open categories codes file "%s"', $this->getCategoriesCodesFixturesPath())]);
            return [];
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
            $this->logger->error(self::INSTALL_ERROR_LOG, ['message' => sprintf('No code found for category "%s"', $filterCategoryId)]);
            return $filterCategoryId;
        }

        $query = <<<SQL
SELECT id FROM pim_catalog_category WHERE code = :code;
SQL;

        $realCategoryId = $this->dbConnection->executeQuery($query, ['code' => $categoriesCodes[$filterCategoryId]])->fetchColumn();
        if (!$realCategoryId) {
            $this->logger->error(self::INSTALL_ERROR_LOG, ['message' => sprintf('No id found for category "%s"', $categoriesCodes[$filterCategoryId])]);
            return $filterCategoryId;
        }

        return $realCategoryId;
    }
}
