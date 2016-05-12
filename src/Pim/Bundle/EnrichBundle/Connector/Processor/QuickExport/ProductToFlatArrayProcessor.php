<?php

namespace Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport;

use Akeneo\Component\Batch\Item\InvalidItemException;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
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

    /** @var string */
    protected $uploadDirectory;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var  ObjectDetacherInterface */
    protected $objectDetacher;

    /**
     * @param SerializerInterface        $serializer
     * @param ChannelRepositoryInterface $channelRepository
     * @param ProductBuilderInterface    $productBuilder
     * @param ObjectDetacherInterface    $objectDetacher
     * @param UserProviderInterface      $userProvider
     * @param TokenStorageInterface      $tokenStorage
     * @param string                     $uploadDirectory
     */
    public function __construct(
        SerializerInterface $serializer,
        ChannelRepositoryInterface $channelRepository,
        ProductBuilderInterface $productBuilder,
        ObjectDetacherInterface $objectDetacher,
        UserProviderInterface $userProvider,
        TokenStorageInterface $tokenStorage,
        $uploadDirectory
    ) {
        $this->serializer        = $serializer;
        $this->channelRepository = $channelRepository;
        $this->productBuilder    = $productBuilder;
        $this->objectDetacher    = $objectDetacher;
        $this->userProvider      = $userProvider;
        $this->tokenStorage      = $tokenStorage;
        $this->uploadDirectory   = $uploadDirectory;
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
                    [
                        'item'            => $product->getIdentifier()->getData(),
                        'uploadDirectory' => $this->uploadDirectory,
                    ]
                );
            }
        }

        $data['product'] = $this->serializer->normalize($product, 'flat', $this->getNormalizerContext());
        $this->objectDetacher->detach($product);

        return $data;
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

        if (!isset($mainContext['scope'])) {
            throw new \InvalidArgumentException('No channel found');
        }

        if (!isset($mainContext['ui_locale'])) {
            throw new \InvalidArgumentException('No UI locale found');
        }

        $normalizerContext = [
            'scopeCode'   => $mainContext['scope'],
            'localeCodes' => $this->getLocaleCodes($mainContext['scope']),
            'locale'      => $mainContext['ui_locale'],
            'filter_types' => [
                'pim.transform.product_value.flat',
                'pim.transform.product_value.flat.quick_export'
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
