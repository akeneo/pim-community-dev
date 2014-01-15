<?php

namespace Pim\Bundle\DashboardBundle\Widget;

use Doctrine\ORM\EntityManager;

/**
 * Widget to display completeness of products over channels and locales
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessWidget implements WidgetInterface
{
    /** @var EntityManager */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
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
        $repo = $this->entityManager->getRepository('PimCatalogBundle:Channel');
        $channels = $repo->countProducts();
        $completeProducts = $repo->countCompleteProducts();

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
