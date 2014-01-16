<?php

namespace Pim\Bundle\DashboardBundle\Widget;

use Pim\Bundle\CatalogBundle\Model\ProductRepositoryInterface;

/**
 * Widget to display completeness of products over channels and locales
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessWidget implements WidgetInterface
{
    /** @var ProductRepositoryInterface */
    protected $repository;

    /**
     * @param ProductRepositoryInterface $repository
     */
    public function __construct(ProductRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return 'PimDashboardBundle:Widget:completeness.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        $channels = $this->repository->countProductsPerChannels();
        $completeProducts = $this->repository->countCompleteProductsPerChannels();

        $params = array();
        foreach ($channels as $channel) {
            $params[$channel['label']] = array(
                'total' => $channel['total'],
                'complete' => 0,
            );
        }
        foreach ($completeProducts as $completeProduct) {
            $params[$completeProduct['label']]['locales'][$completeProduct['locale']] = $completeProduct['total'];
            $params[$completeProduct['label']]['complete'] += $completeProduct['total'];
        }

        return array(
            'params' => $params
        );
    }
}
