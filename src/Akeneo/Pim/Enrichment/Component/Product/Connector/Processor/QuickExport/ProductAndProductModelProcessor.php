<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\QuickExport;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\FilterValues;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AbstractProcessor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingProductModelValues;
use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingProductValues;
use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingValuesInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Processor\BulkMediaFetcher;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product and Product Model processor to process and normalize entities to the standard format.
 * This class is only used for Quick Export feature.
 *
 * This processor doesn't use the channel in configuration field but from job configuration
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAndProductModelProcessor extends AbstractProcessor
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var FillMissingValuesInterface */
    protected $fillMissingProductModelValues;

    /** @var FillMissingValuesInterface */
    protected $fillMissingProductValues;

    /** @var ObjectDetacherInterface */
    protected $detacher;

    /** @var UserProviderInterface */
    protected $userProvider;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var BulkMediaFetcher */
    protected $mediaFetcher;

    public function __construct(
        NormalizerInterface $normalizer,
        ChannelRepositoryInterface $channelRepository,
        AttributeRepositoryInterface $attributeRepository,
        FillMissingValuesInterface $fillMissingProductModelValues,
        FillMissingValuesInterface $fillMissingProductValues,
        ObjectDetacherInterface $detacher,
        UserProviderInterface $userProvider,
        TokenStorageInterface $tokenStorage,
        BulkMediaFetcher $mediaFetcher
    ) {
        $this->normalizer = $normalizer;
        $this->channelRepository = $channelRepository;
        $this->attributeRepository = $attributeRepository;
        $this->fillMissingProductModelValues = $fillMissingProductModelValues;
        $this->fillMissingProductValues = $fillMissingProductValues;
        $this->detacher = $detacher;
        $this->userProvider = $userProvider;
        $this->tokenStorage = $tokenStorage;
        $this->mediaFetcher = $mediaFetcher;
    }

    /**
     * {@inheritdoc}
     */
    public function process($entityWithValues)
    {
        $this->initSecurityContext($this->stepExecution);

        $parameters = $this->stepExecution->getJobParameters();
        $normalizerContext = $this->getNormalizerContext($parameters);
        $productStandard = $this->normalizer->normalize($entityWithValues, 'standard', $normalizerContext);

        if ($entityWithValues instanceof ProductInterface) {
            $productStandard = $this->fillMissingProductValues->fromStandardFormat($productStandard);
        } else {
            $productStandard = $this->fillMissingProductModelValues->fromStandardFormat($productStandard);
        }

        $locales = $parameters->has('selected_locales') ?
            $parameters->get('selected_locales') :
            $this->getLocaleCodes($parameters->get('scope'));

        $productStandard['values'] = FilterValues::create()
            ->filterByChannelCode($parameters->get('scope'))
            ->filterByLocaleCodes($locales)
            ->execute($productStandard['values']);

        $selectedProperties = $parameters->get('selected_properties');

        if ($this->areAttributesToFilter($parameters)) {
            if (in_array('identifier', $selectedProperties)) {
                $identifier = $this->attributeRepository->findOneBy(['type' => AttributeTypes::IDENTIFIER]);
                $selectedProperties[] = $identifier->getCode();
                $selectedProperties[] = 'code';
            }
            if (in_array('family', $selectedProperties)) {
                $selectedProperties[] = 'family_variant';
            }
            $productStandard = $this->filterProperties($productStandard, $selectedProperties);
        }

        if ($parameters->has('with_media') && $parameters->get('with_media')) {
            $directory = $this->stepExecution->getJobExecution()->getExecutionContext()
                ->get(JobInterface::WORKING_DIRECTORY_PARAMETER);

            $identifier = ($entityWithValues instanceof ProductInterface)
                ? $entityWithValues->getIdentifier()
                : $entityWithValues->getCode();

            $entityValues = $this->areAttributesToFilter($parameters)
                ? $this->filterValues($entityWithValues->getValues(), $selectedProperties)
                : $entityWithValues->getValues();

            $this->mediaFetcher->fetchAll($entityValues, $directory, $identifier);

            foreach ($this->mediaFetcher->getErrors() as $error) {
                $this->stepExecution->addWarning($error['message'], [], new DataInvalidItem($error['media']));
            }
        }

        $this->detacher->detach($entityWithValues);

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
            } elseif (in_array($codeProperty, $selectedProperties) || 'identifier' === $codeProperty) {
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
            'locales'      => $parameters->has('selected_locales') ?
                $parameters->get('selected_locales') :
                $this->getLocaleCodes($parameters->get('scope')),
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
     * Filter values to keep only those that are defined by the context.
     *
     * @param WriteValueCollection $values
     * @param array                    $selectedAttributes
     *
     * @return WriteValueCollection
     */
    protected function filterValues(WriteValueCollection $values, array $selectedAttributes)
    {
        return $values->filter(function ($productValue) use ($selectedAttributes) {
            $attributeCode = $productValue->getAttributeCode();

            return in_array($attributeCode, $selectedAttributes);
        });
    }
}
