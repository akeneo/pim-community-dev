<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamworkAssistantBundle\Doctrine\ORM;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\TableNameBuilder;

/**
 * Ease overriding entities managing with DBAL support avoiding hard-coded table names.
 *
 * @see Akeneo\Bundle\StorageUtilsBundle\Doctrine\TableNameBuilder.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class TableNameMapper
{
    /** @var TableNameBuilder */
    protected $tableNameBuilder;

    /** @var string[] */
    protected $preProcessingTables;

    /**
     * @param TableNameBuilder $tableNameBuilder
     * @param string[]         $preProcessingTables
     */
    public function __construct(TableNameBuilder $tableNameBuilder, array $preProcessingTables)
    {
        $this->tableNameBuilder = $tableNameBuilder;
        $this->preProcessingTables = $preProcessingTables;
    }

    /**
     * Analyse a native query to update the sql table names (they are found in the doctrine mapping).
     *
     * "@pim_user.entity.user@" will find the SQL table name for the entity user (container parameter: pim_user.entity.user.class)
     * "@pim_user.entity.user#groups@" same logic but the join SQL table name (N-N between user and group)
     *
     * @param string $sql
     *
     * @throws \LogicException if a SQL table name cannot be found depending on the given key
     *
     * @return string
     */
    public function createQuery($sql)
    {
        return preg_replace_callback(
            "/@([^@]*)@/",
            function ($matches) {
                $targetEntity = null;
                $key = $matches[1];
                if (strpos($key, '#')) {
                    list($key, $targetEntity) = explode('#', $key);
                }

                return $this->getTableName($key, $targetEntity);
            },
            $sql
        );
    }

    /**
     * Get the table name from the entity parameter name
     *
     * @param string      $key
     * @param string|null $targetEntity
     *
     * @throws \LogicException if a SQL table name cannot be found depending the given key
     *
     * @return string
     */
    public function getTableName($key, $targetEntity = null)
    {
        $completeKey = $key;
        if (null !== $targetEntity) {
            $completeKey = sprintf('%s#%s', $key, $targetEntity);
        }

        if (isset($this->preProcessingTables[$completeKey])) {
            return $this->preProcessingTables[$completeKey];
        }

        try {
            $key = sprintf('%s.class', $key);
            return $this->tableNameBuilder->getTableName($key, $targetEntity);
        } catch (\Exception $e) {
            throw new \LogicException(
                sprintf('No SQL table mapped to "%s" key or the target entity "%s"', $key, $targetEntity)
            );
        }
    }
}
