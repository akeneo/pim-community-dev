<?php

namespace Pim\Bundle\DataGridBundle\EventListener;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration as SorterConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

/**
 * Hide columns the user configured
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HideColumnsListener
{
    const AVAILABLE_COLUMNS = 'availableColumns';

    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var EntityRepository */
    protected $repository;

    /**
     * @param SecurityContextInterface $securityContext
     * @param EntityRepository         $repository
     */
    public function __construct(SecurityContextInterface $securityContext, EntityRepository $repository)
    {
        $this->securityContext = $securityContext;
        $this->repository      = $repository;
    }

    /**
     * Remove columns hidden by current user from datagrid metadata
     *
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $config = $event->getDatagrid()->getAcceptor()->getConfig();

        $datagridConfig = $this->getDatagridConfig(
            $config->offsetGetByPath(sprintf('[%s]', DatagridConfiguration::NAME_KEY))
        );

        $availableColumns = [];
        $columns = [];
        $sorters = [];
        foreach ($config->offsetGetByPath(sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY)) as $key => $metadata) {
            $availableColumns[$key] = $metadata['label'];
            if ($datagridConfig && in_array($key, $datagridConfig->getColumns())) {
                $columns[$key] = $metadata;
                $sorters[$key] = $config->offsetGetByPath(sprintf('%s[%s]', SorterConfiguration::COLUMNS_PATH, $key));
            }
        }
        $config->offsetSetByPath(sprintf('[%s]', self::AVAILABLE_COLUMNS), $availableColumns);
        if ($datagridConfig) {
            $sortedColumns = [];
            foreach ($datagridConfig->getColumns() as $column) {
                if (array_key_exists($column, $columns)) {
                    $sortedColumns[$column] = $columns[$column];
                }
            }
            $config->offsetSetByPath(sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY), $sortedColumns);
            $config->offsetSetByPath(sprintf('%s', SorterConfiguration::COLUMNS_PATH), $sorters);
        }
    }

    /**
     * Get datagrid configuration
     *
     * @param string $alias
     *
     * @return null|DatagridConfiguration
     */
    protected function getDatagridConfig($alias)
    {
        return $this
            ->repository
            ->findOneBy(
                [
                    'datagridAlias' => $alias,
                    'user'          => $this->getUser(),
                ]
            );
    }

    /**
     * Get the user from the security context
     *
     * @return null|User
     */
    protected function getUser()
    {
        if (null === $token = $this->securityContext->getToken()) {
            return;
        }

        if (!is_object($user = $token->getUser())) {
            return;
        }

        return $user;
    }
}
