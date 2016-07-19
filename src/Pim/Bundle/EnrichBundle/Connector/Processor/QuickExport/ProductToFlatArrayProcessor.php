<?php

namespace Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\Batch\Item\InvalidItemException;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Pim\Bundle\CatalogBundle\ProductQueryUtility;
use Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\FieldSplitter;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Process a product to a flat array
 *
 * This processor doesn't use the channel in configuration field but from job configuration
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductToFlatArrayProcessor extends AbstractProcessor
{
    /** @var SerializerInterface */
    protected $serializer;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var  ObjectDetacherInterface */
    protected $objectDetacher;

    /** @var UserProviderInterface */
    protected $userProvider;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var FieldSplitter */
    protected $fieldSplitter;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var string */
    protected $uploadDirectory;

    /**
     * @param SerializerInterface          $serializer
     * @param ChannelRepositoryInterface   $channelRepository
     * @param ProductBuilderInterface      $productBuilder
     * @param ObjectDetacherInterface      $objectDetacher
     * @param UserProviderInterface        $userProvider
     * @param TokenStorageInterface        $tokenStorage
     * @param FieldSplitter                $fieldSplitter
     * @param AttributeRepositoryInterface $attributeRepository
     * @param string                       $uploadDirectory
     */
    public function __construct(
        SerializerInterface $serializer,
        ChannelRepositoryInterface $channelRepository,
        ProductBuilderInterface $productBuilder,
        ObjectDetacherInterface $objectDetacher,
        UserProviderInterface $userProvider,
        TokenStorageInterface $tokenStorage,
        FieldSplitter $fieldSplitter,
        AttributeRepositoryInterface $attributeRepository,
        $uploadDirectory
    ) {
        $this->serializer          = $serializer;
        $this->channelRepository   = $channelRepository;
        $this->productBuilder      = $productBuilder;
        $this->objectDetacher      = $objectDetacher;
        $this->userProvider        = $userProvider;
        $this->tokenStorage        = $tokenStorage;
        $this->fieldSplitter       = $fieldSplitter;
        $this->attributeRepository = $attributeRepository;
        $this->uploadDirectory     = $uploadDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $this->initSecurityContext($this->stepExecution);

        if (null !== $this->productBuilder) {
            $this->productBuilder->addMissingProductValues($product);
        }

        $data['media'] = [];

        $productMedias = $this->getProductMedias($product);
        if (count($productMedias) > 0) {
            try {
                $data['media'] = $this->serializer->normalize(
                    $productMedias,
                    'flat',
                    ['field_name' => 'media', 'prepare_copy' => true]
                );
            } catch (FileNotFoundException $e) {
                throw new InvalidItemException(
                    $e->getMessage(),
                    new DataInvalidItem($product)
                );
            }
        }

        $normalizerContext = $this->getNormalizerContext();
        $data['product'] = $this->serializer->normalize($product, 'flat', $normalizerContext);

        if (isset($normalizerContext['selected_properties']) && !empty($normalizerContext['selected_properties'])) {
            $data['product'] = $this->filterProperties(
                $product,
                $data['product'],
                $normalizerContext['selected_properties'],
                $normalizerContext['locale'],
                $normalizerContext['scopeCode']
            );
        }

        $this->objectDetacher->detach($product);

        return $data;
    }

    /**
     * filter the properties that has to be exported based on a product and a list of properties
     *
     * @param ProductInterface $product
     * @param array            $normalizedProduct
     * @param array            $properties
     * @param string           $localeCode
     * @param string           $channelCode
     *
     * @return array
     */
    protected function filterProperties(
        ProductInterface $product,
        array $normalizedProduct,
        array $properties,
        $localeCode = null,
        $channelCode = null
    ) {
        $filteredColumnList = [];

        foreach ($properties as $column) {
            if ('label' === $column && null !== $product->getFamily()) {
                $column = $product->getFamily()->getAttributeAsLabel()->getCode();
            }

            $attribute = $this->getProductAttributeByCode($product, $column);
            if (null !== $attribute) {
                if (in_array(
                    $attribute->getAttributeType(),
                    [AttributeTypes::PRICE_COLLECTION, AttributeTypes::METRIC]
                )) {
                    foreach ($normalizedProduct as $key => $value) {
                        if ($column === $this->fieldSplitter->splitFieldName($key)[0]) {
                            $filteredColumnList[] = $key;
                        }
                    };
                } else {
                    $filteredColumnList[] = ProductQueryUtility::getNormalizedValueFieldFromAttribute(
                        $attribute,
                        $localeCode,
                        $channelCode
                    );
                }
            } else {
                $filteredColumnList[] = $column;
            }
        }

        return array_intersect_key($normalizedProduct, array_flip($filteredColumnList));
    }

    /**
     * @param ProductInterface $product
     * @param string           $attributeCode
     *
     * @return AttributeInterface|null
     */
    protected function getProductAttributeByCode(ProductInterface $product, $attributeCode)
    {
        foreach ($product->getValues() as $value) {
            if ($attributeCode === $value->getAttribute()->getCode()) {
                return $value->getAttribute();
            }
        }

        return null;
    }

    /**
     * @param ProductInterface $product
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function getNormalizerContext(ProductInterface $product)
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $mainContext = $jobParameters->get('mainContext');
        $columns = $jobParameters->get('selected_properties');

        if (!isset($mainContext['scope'])) {
            throw new \InvalidArgumentException('No channel found');
        }

        if (!isset($mainContext['ui_locale'])) {
            throw new \InvalidArgumentException('No UI locale found');
        }

        if (isset($columns) && 0 !== count($columns)) {
            $columns[] = $this->attributeRepository->getIdentifier();
        }

        $normalizerContext = [
            'scopeCode'           => $mainContext['scope'],
            'localeCodes'         => $this->getLocaleCodes($mainContext['scope']),
            'locale'              => $mainContext['ui_locale'],
            'filter_types'        => [
                'pim.transform.product_value.flat',
                'pim.transform.product_value.flat.quick_export'
            ],
            'selected_properties' => $columns
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
     * Fetch product media
     *
     * @param ProductInterface $product
     *
     * @return FileInfoInterface[]
     */
    protected function getProductMedias(ProductInterface $product)
    {
        $media = [];
        foreach ($product->getValues() as $value) {
            if (in_array($value->getAttribute()->getAttributeType(), [AttributeTypes::IMAGE, AttributeTypes::FILE])) {
                $media[] = $value->getData();
            }
        }

        return $media;
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
