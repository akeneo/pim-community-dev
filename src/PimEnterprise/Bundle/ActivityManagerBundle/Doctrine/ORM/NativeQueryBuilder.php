<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\TableNameBuilder;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class NativeQueryBuilder
{
    /** @var TableNameBuilder */
    protected $tableNameBuilder;

    /** @var string[] */
    protected $preProcessingTables;

    /**
     * @param TableNameBuilder $tableNameBuilder
     * @param string[]         $preProcessingTables
     */
    public function __construct(TableNameBuilder $tableNameBuilder, $preProcessingTables)
    {
        $this->tableNameBuilder = $tableNameBuilder;
        $this->preProcessingTables = $preProcessingTables;
    }

    /**
     * @param string $sql
     *
     * @throws \LogicException
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
     * @param string $key
     * @param string $targetEntity
     *
     * @throws \LogicException
     *
     * @return string
     */
    public function getTableName($key, $targetEntity = null)
    {
        if (isset($this->preProcessingTables[$key])) {
            return $this->preProcessingTables[$key];
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
