<?php

namespace Oro\Bundle\DataGridBundle\Extension\Pager;

/**
 * Dummy pager that does absolutely nothing :)
 *
 * This is needed for instance when Elasticsearch is used to return the results of the datagrid. In
 * that case, the pagination is handled internally by Elasticsearch, and we don't need to handle it
 * in the pager.
 * The pager is still necessary though, to be able to store the current page and the current number
 * of items per page. To learn more, {@see Oro\Bundle\PimDataGridBundle\Extension\Pager\PagerExtension::visitDatasource}
 *
 * #bestCodeEver #soProud
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DummyPager implements PagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxPerPage(int $maxPerPage): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxPerPage(): int
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setPage(int $page): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getPage(): int
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getNbResults(): int
    {
    }

    /**
     * @param mixed $qb
     */
    public function setQueryBuilder($qb): self
    {
        return $this;
    }
}
