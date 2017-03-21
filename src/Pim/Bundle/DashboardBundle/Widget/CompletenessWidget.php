<?php

namespace Pim\Bundle\DashboardBundle\Widget;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Repository\CompletenessRepositoryInterface;

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

    /** @var UserContext */
    protected $userContext;

    /** @var ObjectFilterInterface */
    protected $objectFilter;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $localeRepository;

    /**
     * @param CompletenessRepositoryInterface       $completenessRepo
     * @param LocaleHelper                          $localeHelper
     * @param UserContext                           $userContext
     * @param ObjectFilterInterface                 $objectFilter
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     */
    public function __construct(
        CompletenessRepositoryInterface $completenessRepo,
        LocaleHelper $localeHelper,
        UserContext $userContext,
        ObjectFilterInterface $objectFilter,
        IdentifiableObjectRepositoryInterface $localeRepository
    ) {
        $this->completenessRepo = $completenessRepo;
        $this->localeHelper     = $localeHelper;
        $this->userContext      = $userContext;
        $this->objectFilter     = $objectFilter;
        $this->localeRepository = $localeRepository;
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
        $userLocale = $this->userContext->getCurrentLocaleCode();
        $channels = $this->completenessRepo->getProductsCountPerChannels($userLocale);
        $completeProducts = $this->completenessRepo->getCompleteProductsCountPerChannels($userLocale);

        $data = [];
        foreach ($channels as $channel) {
            $data[$channel['label']] = [
                'total'    => (int) $channel['total'],
                'complete' => 0,
            ];
        }
        foreach ($completeProducts as $completeProduct) {
            $locale = $this->localeRepository->findOneByIdentifier($completeProduct['locale']);
            if (!$this->objectFilter->filterObject($locale, 'pim.internal_api.locale.view')) {
                $localeLabel = $this->localeHelper->getLocaleLabel($completeProduct['locale']);
                $data[$completeProduct['label']]['locales'][$localeLabel] = (int) $completeProduct['total'];
                $data[$completeProduct['label']]['complete'] += $completeProduct['total'];
            }
        }

        $data = array_filter($data, function ($channel) {
            return isset($channel['locales']);
        });

        return $data;
    }
}
