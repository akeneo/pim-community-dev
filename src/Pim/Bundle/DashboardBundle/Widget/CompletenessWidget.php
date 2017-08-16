<?php

namespace Pim\Bundle\DashboardBundle\Widget;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
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

    /** @var UserContext */
    protected $userContext;

    /** @var ObjectFilterInterface */
    protected $objectFilter;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $localeRepository;

    /**
     * @param CompletenessRepositoryInterface       $completenessRepo
     * @param UserContext                           $userContext
     * @param ObjectFilterInterface                 $objectFilter
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     */
    public function __construct(
        CompletenessRepositoryInterface $completenessRepo,
        UserContext $userContext,
        ObjectFilterInterface $objectFilter,
        IdentifiableObjectRepositoryInterface $localeRepository
    ) {
        $this->completenessRepo = $completenessRepo;
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
                $localeLabel = $this->getCurrentLocaleLabel($completeProduct['locale']);
                $data[$completeProduct['label']]['locales'][$localeLabel] = (int) $completeProduct['total'];
                $data[$completeProduct['label']]['complete'] += $completeProduct['total'];
            }
        }

        $data = array_filter($data, function ($channel) {
            return isset($channel['locales']);
        });

        return $data;
    }

    /**
     * Returns the label of a locale in the specified language
     *
     * @param string $code        the code of the locale to translate
     * @param string $translateIn the locale in which the label should be translated (if null, user locale will be used)
     *
     * @return string
     */
    private function getCurrentLocaleLabel($code, $translateIn = null)
    {
        $translateIn = $translateIn ?: $this->userContext->getCurrentLocaleCode();

        return \Locale::getDisplayName($code, $translateIn);
    }
}
