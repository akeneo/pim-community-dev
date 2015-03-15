<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\Denormalization;

use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Pim\Bundle\BaseConnectorBundle\Reader\File\Formater\CsvFormater;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
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

    /** @var CsvFormater */
    protected $csvFormater;

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
     * @param CsvFormater                           $csvFormater    csv formater
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
        CsvFormater $csvFormater,
        ProductUpdaterInterface $productUpdater,
        $class,
        $format
    ) {
        parent::__construct($repository, $denormalizer, $validator, $detacher, $class);

        $this->productBuilder = $builder;
        $this->csvFormater    = $csvFormater;
        $this->productUpdater = $productUpdater;
        $this->format         = $format; // TODO: should be used by the converter! format to standard then use updater!
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $convertedItem = $this->csvFormater->convertToStructured($item);
        $identifierProperty = $this->repository->getIdentifierProperties();
        $identifier = $convertedItem[$identifierProperty[0]][0]['data'];
        unset($convertedItem[$this->repository->getIdentifierProperties()[0]]);
        unset($convertedItem['associations']);

        $product = $this->findOrCreateProduct($identifier);
        $this->updateProduct($product, $convertedItem);
        $this->validateProduct($product, $item);

        return $product;
    }

    /**
     * @param string $identifier
     *
     * @return ProductInterface
     */
    public function findOrCreateProduct($identifier)
    {
        $product = $this->repository->findOneByIdentifier($identifier);
        if (false === $product) {
            $product = $this->productBuilder->createProduct($identifier);

            // TODO create with family to be able to add values!
        }

        return $product;
    }

    /**
     * @param ProductInterface $product
     * @param array            $item
     */
    protected function updateProduct(ProductInterface $product, array $item)
    {
        // add missing values to ensure product has value for any attribute of its family
        $this->productBuilder->addMissingProductValues($product);
        foreach ($item as $field => $values) {
            if (in_array($field, ['enabled', 'categories', 'groups', 'family'])) {
                $this->productUpdater->setData($product, $field, $values, []);
            } else {
                foreach ($values as $value) {
                    // sets value if it exists, means coming from family's attributes or already exist as optional
                    // TODO no values sets when new product is created (because family has not been sets and values does not exist yet)!
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
