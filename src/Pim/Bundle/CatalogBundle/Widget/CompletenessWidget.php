<?php

namespace Pim\Bundle\CatalogBundle\Widget;

use Doctrine\ORM\EntityManager;

class CompletenessWidget implements WidgetInterface
{
    /** @var EntityManager */
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getTemplate()
    {
        return 'PimCatalogBundle:Widget:completeness.html.twig';
    }

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
