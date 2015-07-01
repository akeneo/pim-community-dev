<?php

namespace Pim\Bundle\DashboardBundle\Widget;

use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;
use Pim\Bundle\CatalogBundle\Repository\CompletenessRepositoryInterface;

/**
 * Widget to display completeness of products over channels and locales
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessWidget implements WidgetInterface
{
    /** @var CompletenessRepositoryInterface */
    protected $completenessRepo;

    /** @var LocaleHelper */
    protected $localeHelper;

    /**
     * @param CompletenessRepositoryInterface $completenessRepo
     * @param LocaleHelper                    $localeHelper
     */
    public function __construct(CompletenessRepositoryInterface $completenessRepo, LocaleHelper $localeHelper)
    {
        $this->completenessRepo = $completenessRepo;
        $this->localeHelper     = $localeHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'completeness';
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
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $channels = $this->completenessRepo->getProductsCountPerChannels();
        $completeProducts = $this->completenessRepo->getCompleteProductsCountPerChannels();

        $data = [];
        foreach ($channels as $channel) {
            $data[$channel['label']] = [
                'total'    => (int) $channel['total'],
                'complete' => 0,
            ];
        }
        foreach ($completeProducts as $completeProduct) {
            $localeLabel = $this->localeHelper->getLocaleLabel($completeProduct['locale']);
            $data[$completeProduct['label']]['locales'][$localeLabel] = (int) $completeProduct['total'];
            $data[$completeProduct['label']]['complete'] += $completeProduct['total'];
        }

        return $data;
    }
}
