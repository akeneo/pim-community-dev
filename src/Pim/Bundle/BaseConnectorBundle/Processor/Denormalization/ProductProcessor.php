<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\Denormalization;

use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Pim\Bundle\BaseConnectorBundle\Reader\File\Converter\StandardFormatConverterInterface;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Product import processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductProcessor extends AbstractProcessor
{
    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var StandardFormatConverterInterface */
    protected $converter;

    /** @var ProductUpdaterInterface */
    protected $productUpdater;

    /** @var string */
    protected $format;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository     repository to search the object in
     * @param DenormalizerInterface                 $denormalizer   denormalizer used to transform array to object
     * @param ValidatorInterface                    $validator      validator of the object
     * @param ObjectDetacherInterface               $detacher       detacher to remove it from UOW when skip
     * @param ProductBuilderInterface               $builder        product builder
     * @param StandardFormatConverterInterface      $converter      csv converter
     * @param ProductUpdaterInterface               $productUpdater product updater
     * @param string                                $class          class of the object to instanciate in case if need
     * @param string                                $format         format use to denormalize
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        DenormalizerInterface $denormalizer, // TODO: useless here, except if embed the updater or setter registry?
        ValidatorInterface $validator,
        ObjectDetacherInterface $detacher,
        ProductBuilderInterface $builder,
        StandardFormatConverterInterface $converter,
        ProductUpdaterInterface $productUpdater,
        $class,
        $format
    ) {
        parent::__construct($repository, $denormalizer, $validator, $detacher, $class);

        $this->productBuilder = $builder;
        $this->converter      = $converter;
        $this->productUpdater = $productUpdater;
        $this->format         = $format; // TODO: should be used by the converter! format to standard then use updater!
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $convertedItem = $this->converter->convert($item);
        $identifierProperty = $this->repository->getIdentifierProperties();

        $identifier = $convertedItem[$identifierProperty[0]][0]['data'];
        $familyCode = $convertedItem['family'];
        unset($convertedItem[$this->repository->getIdentifierProperties()[0]]);
        unset($convertedItem['associations']);
        unset($convertedItem['family']);
        unset($convertedItem['groups']); // TODO: until we split groups and variant groups

        $product = $this->findOrCreateProduct($identifier, $familyCode);
        $this->updateProduct($product, $convertedItem);
        $this->validateProduct($product, $item);

        return $product;
    }

    /**
     * @param string $identifier
     * @param string $familyCode
     *
     * @return ProductInterface
     */
    public function findOrCreateProduct($identifier, $familyCode)
    {
        $product = $this->repository->findOneByIdentifier($identifier);
        if (false === $product) {
            $product = $this->productBuilder->createProduct($identifier, $familyCode);
        } else {
            // add missing values to ensure product has values for any attribute of its family
            $this->productBuilder->addMissingProductValues($product);
        }

        return $product;
    }

    /**
     * @param ProductInterface $product
     * @param array            $item
     */
    protected function updateProduct(ProductInterface $product, array $item)
    {
        foreach ($item as $field => $values) {
            if (in_array($field, ['enabled', 'categories', 'groups'])) {
                $this->productUpdater->setData($product, $field, $values, []);
            } else {
                foreach ($values as $value) {
                    // sets value if it exists, means coming from family's attributes or already exist as optional
                    if (null !== $product->getValue($field, $value['locale'], $value['scope'])) {
                        $options = ['locale' => $value['locale'], 'scope' => $value['scope']];
                        $this->productUpdater->setData($product, $field, $value['data'], $options);
                    }
                }
            }
        }
    }

    /**
     * @param ProductInterface $product
     * @param array            $item
     */
    protected function validateProduct(ProductInterface $product, array $item)
    {
        $violations = $this->validator->validate($product);
        if ($violations->count() !== 0) {
            $this->detachObject($product);
            $this->skipItemWithConstraintViolations($item, $violations);
        }
    }
}
