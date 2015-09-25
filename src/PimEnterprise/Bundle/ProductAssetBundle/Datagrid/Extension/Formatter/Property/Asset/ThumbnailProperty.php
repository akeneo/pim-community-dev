<?php

namespace PimEnterprise\Bundle\ProductAssetBundle\Datagrid\Extension\Formatter\Property\Asset;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;
use Pim\Bundle\DataGridBundle\Extension\Formatter\Property\ProductValue\TwigProperty;
use Pim\Bundle\EnrichBundle\Controller\FileController;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;

/**
 * Thumbnail property for an asset
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
class ThumbnailProperty extends TwigProperty
{
    /** @var RequestParametersExtractorInterface */
    protected $paramsExtractor;

    /** @var UserContext */
    protected $userContext;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /**
     * @param \Twig_Environment                   $environment
     * @param RequestParametersExtractorInterface $paramsExtractor
     * @param UserContext                         $userContext
     * @param LocaleRepositoryInterface           $localeRepository
     * @param ChannelRepositoryInterface          $channelRepository
     */
    public function __construct(
        \Twig_Environment $environment,
        RequestParametersExtractorInterface $paramsExtractor,
        UserContext $userContext,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository
    ) {
        parent::__construct($environment);

        $this->paramsExtractor   = $paramsExtractor;
        $this->userContext       = $userContext;
        $this->localeRepository  = $localeRepository;
        $this->channelRepository = $channelRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function format($fileInfo)
    {
        $path = $fileInfo instanceof FileInfoInterface ?
            $fileInfo->getKey() :
            FileController::DEFAULT_IMAGE_KEY;

        return $this->getTemplate()->render(['path' => $path]);
    }

    /**
     * Fetch the variation file corresponding to the current locale and channel
     *
     * @param ResultRecordInterface $record
     *
     * @return FileInfoInterface|null
     */
    protected function getRawValue(ResultRecordInterface $record)
    {
        $fileInfo = null;
        $locale   = $this->getCurrentLocale();
        $channel  = $this->getCurrentChannel();
        $entity   = $record->getRootEntity();
        if (null === $entity) {
            return null;
        }

        $fileInfo = $entity->getFileForContext($channel, $locale);
        if (null === $fileInfo) {
            $reference = $entity->getReference($locale);
            if ($reference instanceof ReferenceInterface) {
                $fileInfo = $reference->getFileInfo();
            }
        }

        return $fileInfo;
    }

    /**
     * Return the current locale from datagrid parameters, then request parameters
     *
     * @return LocaleInterface
     */
    protected function getCurrentLocale()
    {
        $localeCode = $this->paramsExtractor->getParameter('dataLocale');

        return $this->localeRepository->findOneByIdentifier($localeCode);
    }

    /**
     * Return the current channel from datagrid parameters, then request parameters, then user config
     *
     * @return ChannelInterface
     */
    protected function getCurrentChannel()
    {
        $scopeCode = null;

        $filterValues = $this->paramsExtractor->getDatagridParameter('_filter');
        if (isset($filterValues['scope']['value'])) {
            $scopeCode = $filterValues['scope']['value'];
        }

        if (null === $scopeCode) {
            $requestFilters = $this->paramsExtractor->getRequestParameter('filters');
            if (isset($requestFilters['scope']['value'])) {
                $scopeCode = $requestFilters['scope']['value'];
            }
        }

        if (null !== $scopeCode) {
            return $this->channelRepository->findOneByIdentifier($scopeCode);
        }

        return $this->userContext->getUserChannel();
    }
}
