<?php

namespace Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
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

    /** @var  ObjectDetacherInterface */
    protected $detacher;

    /** @var UserProviderInterface */
    protected $userProvider;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * @param NormalizerInterface          $normalizer
     * @param ChannelRepositoryInterface   $channelRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ProductBuilderInterface      $productBuilder
     * @param ObjectDetacherInterface      $detacher
     * @param UserProviderInterface        $userProvider
     * @param TokenStorageInterface        $tokenStorage
     */
    public function __construct(
        NormalizerInterface $normalizer,
        ChannelRepositoryInterface $channelRepository,
        AttributeRepositoryInterface $attributeRepository,
        ProductBuilderInterface $productBuilder,
        ObjectDetacherInterface $detacher,
        UserProviderInterface $userProvider,
        TokenStorageInterface $tokenStorage
    ) {
        $this->normalizer = $normalizer;
        $this->channelRepository = $channelRepository;
        $this->attributeRepository = $attributeRepository;
        $this->productBuilder = $productBuilder;
        $this->detacher = $detacher;
        $this->userProvider = $userProvider;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $this->initSecurityContext($this->stepExecution);

        $this->productBuilder->addMissingProductValues($product);

        $normalizerContext = $this->getNormalizerContext();
        $productStandard = $this->normalizer->normalize($product, 'json', $normalizerContext);
        $productStandard = $this->filterProperties(
            $productStandard,
            $normalizerContext['localeCodes'],
            $normalizerContext['scopeCode'],
            $normalizerContext['selected_properties']
        );

        $this->detacher->detach($product);

        return $productStandard;
    }

    /**
     * Filter values to keep only selected values with scope & locales defined by context
     *
     * @param array      $product
     * @param array      $localeCodes
     * @param string     $channelCode
     * @param array|null $selectedProperties
     *
     * @return array
     */
    protected function filterProperties(
        array $product,
        array $localeCodes,
        $channelCode,
        array $selectedProperties = null
    ) {
        $propertiesToExport = [];
        foreach ($product as $codeProperty => $property) {
            if ('values' === $codeProperty) {
                $propertiesToExport['values'] = $this->filterValues(
                    $property,
                    $localeCodes,
                    $channelCode,
                    $selectedProperties
                );
            } elseif (null === $selectedProperties || in_array($codeProperty, $selectedProperties)) {
                $propertiesToExport[$codeProperty] = $property;
            }
        }

        return $propertiesToExport;
    }

    /**
     * @param array      $values
     * @param array      $localeCodes
     * @param string     $channelCode
     * @param array|null $selectedProperties
     *
     * @return array
     */
    protected function filterValues(
        array $values,
        array $localeCodes,
        $channelCode,
        array $selectedProperties = null
    ) {
        $valuesToExport = [];
        foreach ($values as $code => $value) {
            if (null === $selectedProperties || in_array($code, $selectedProperties)) {
                $valuesToExport[$code] = array_filter(
                    $value,
                    function ($data) use ($channelCode, $localeCodes) {
                        $keepScope  = null === $data['scope'] || $data['scope'] === $channelCode;
                        $keepLocale = null === $data['locale'] || in_array($data['locale'], $localeCodes);

                        return $keepScope && $keepLocale;
                    }
                );
            }
        }

        return $valuesToExport;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function getNormalizerContext()
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $mainContext = $jobParameters->get('mainContext');
        $columns = $jobParameters->get('selected_properties');

        if (!isset($mainContext['scope'])) {
            throw new \InvalidArgumentException('No channel found');
        }

        if (isset($columns) && 0 !== count($columns)) {
            $columns[] = $this->attributeRepository->getIdentifierCode();
        }

        $normalizerContext = [
            'scopeCode'           => $mainContext['scope'],
            'localeCodes'         => $this->getLocaleCodes($mainContext['scope']),
            'selected_properties' => $columns,
            'filter_types'        => [
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
}
