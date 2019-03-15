<?php
declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Widget;

use Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface;

/**
 * Widget to display last import/export operations
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LastOperationsWidget implements WidgetInterface
{
    /** @var LastOperationsFetcher */
    private $fetcher;

    public function __construct(LastOperationsFetcher $fetcher)
    {
        $this->fetcher = $fetcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'last_operations';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return 'PimImportExportBundle:Widget:last_operations.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->fetcher->fetch();
    }
}
