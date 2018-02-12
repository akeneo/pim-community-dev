<?php

namespace Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Connector\Processor\BulkMediaFetcher;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product processor to process and normalize entities to the standard format
 *
 * This processor doesn't use the channel in configuration field but from job configuration
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductProcessor extends AbstractProcessor
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var ObjectDetacherInterface */
    protected $detacher;

    /** @var UserProviderInterface */
    protected $userProvider;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var BulkMediaFetcher */
    protected $mediaFetcher;

    /**
     * @param NormalizerInterface          $normalizer
     * @param ChannelRepositoryInterface   $channelRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ProductBuilderInterface      $productBuilder
     * @param ObjectDetacherInterface      $detacher
     * @param UserProviderInterface        $userProvider
     * @param TokenStorageInterface        $tokenStorage
     * @param BulkMediaFetcher             $mediaFetcher
     */
    public function __construct(
        NormalizerInterface $normalizer,
        ChannelRepositoryInterface $channelRepository,
        AttributeRepositoryInterface $attributeRepository,
        ProductBuilderInterface $productBuilder,
        ObjectDetacherInterface $detacher,
        UserProviderInterface $userProvider,
        TokenStorageInterface $tokenStorage,
        BulkMediaFetcher $mediaFetcher
    ) {
        $this->normalizer = $normalizer;
        $this->channelRepository = $channelRepository;
        $this->attributeRepository = $attributeRepository;
        $this->productBuilder = $productBuilder;
        $this->detacher = $detacher;
        $this->userProvider = $userProvider;
        $this->tokenStorage = $tokenStorage;
        $this->mediaFetcher = $mediaFetcher;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $this->initSecurityContext($this->stepExecution);

        $this->productBuilder->addMissingProductValues($product);

        $parameters = $this->stepExecution->getJobParameters();
        $normalizerContext = $this->getNormalizerContext($parameters);
        $productStandard = $this->normalizer->normalize($product, 'json', $normalizerContext);

        if ($this->areAttributesToFilter($parameters)) {
            $productStandard = $this->filterProperties($productStandard, $parameters->get('selected_properties'));
        }

        if ($parameters->has('with_media') && $parameters->get('with_media')) {
            $directory = $this->stepExecution->getJobExecution()->getExecutionContext()
                ->get(JobInterface::WORKING_DIRECTORY_PARAMETER);

            $productValues = $this->areAttributesToFilter($parameters)
                ? $this->filterProductValues($product, $parameters->get('selected_properties'))
                : $product->getValues();

            $this->fetchMedia($product, $directory, $productValues);
        }

        $this->detacher->detach($product);

        return $productStandard;
    }

    /**
     * Filter properties to keep only properties defined by context
     *
     * @param array $product
     * @param array $selectedProperties
     *
     * @return array
     */
    protected function filterProperties(array $product, array $selectedProperties)
    {
        $propertiesToExport = [];
        foreach ($product as $codeProperty => $property) {
            if ('values' === $codeProperty) {
                $propertiesToExport['values'] = array_filter(
                    $property,
                    function ($attributeCode) use ($selectedProperties) {
                        return in_array($attributeCode, $selectedProperties);
                    }, ARRAY_FILTER_USE_KEY
                );
            } elseif (in_array($codeProperty, $selectedProperties)) {
                $propertiesToExport[$codeProperty] = $property;
            }
        }

        return $propertiesToExport;
    }

    /**
     * Are there properties to filters ?
     *
     * @param JobParameters $parameters
     *
     * @return bool
     */
    protected function areAttributesToFilter(JobParameters $parameters)
    {
        return null !== $parameters->get('selected_properties');
    }

    /**
     * Fetch medias on the local filesystem for given product values.
     *
     * @param ProductInterface $product
     * @param string           $directory
     * @param Collection       $productValues
     */
    protected function fetchMedia(ProductInterface $product, $directory, Collection $productValues)
    {
        $identifier = $product->getIdentifier()->getData();
        $this->mediaFetcher->fetchAll($productValues, $directory, $identifier);

        foreach ($this->mediaFetcher->getErrors() as $error) {
            $this->stepExecution->addWarning($error['message'], [], new DataInvalidItem($error['media']));
        }
    }

    /**
     * @param JobParameters $parameters
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function getNormalizerContext(JobParameters $parameters)
    {
        if (!$parameters->has('scope')) {
            throw new \InvalidArgumentException('No channel found');
        }

        $normalizerContext = [
            'channels'     => [$parameters->get('scope')],
            'locales'      => $this->getLocaleCodes($parameters->get('scope')),
            'filter_types' => [
                'pim.transform.product_value.structured',
                'pim.transform.product_value.structured.quick_export'
            ]
        ];

        return $normalizerContext;
    }

    /**
     * Get locale codes for a channel
     *
     * @param string $channelCode
     *
     * @return array
     */
    protected function getLocaleCodes($channelCode)
    {
        $channel = $this->channelRepository->findOneByIdentifier($channelCode);

        return $channel->getLocaleCodes();
    }

    /**
     * Initialize the SecurityContext from the given $stepExecution
     *
     * @param StepExecution $stepExecution
     */
    protected function initSecurityContext(StepExecution $stepExecution)
    {
        $username = $stepExecution->getJobExecution()->getUser();
        $user = $this->userProvider->loadUserByUsername($username);

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);
    }

    /**
     * Filter the product values to keep only those that are selected from the context.
     *
     * @param ProductInterface $product
     * @param array            $selectedAttributes
     *
     * @return Collection
     */
    protected function filterProductValues(ProductInterface $product, array $selectedAttributes)
    {
        $productValues = $product->getValues();

        return $productValues->filter(function ($productValue) use ($selectedAttributes) {
            $attributeCode = $productValue->getAttribute()->getCode();

            return in_array($attributeCode, $selectedAttributes);
        });
    }
}
