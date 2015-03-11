<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\Denormalization;

use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Pim\Bundle\BaseConnectorBundle\Reader\File\Formater\CsvFormater;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
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
    /** @var ProductManager */
    protected $manager;

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
     * @param ProductManager                        $manager        product manager
     * @param CsvFormater                           $csvFormater    product manager
     * @param ProductUpdaterInterface               $productUpdater product manager
     * @param string                                $class          class of the object to instanciate in case if need
     * @param string                                $format         format use to denormalize
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        DenormalizerInterface $denormalizer, // TODO: useless here, except if embed the updater or setter registry?
        ValidatorInterface $validator,
        ObjectDetacherInterface $detacher,
        ProductManager $manager,
        CsvFormater $csvFormater,
        ProductUpdaterInterface $productUpdater,
        $class,
        $format
    ) {
        parent::__construct($repository, $denormalizer, $validator, $detacher, $class);

        $this->manager        = $manager; // TODO: should be builder
        $this->csvFormater    = $csvFormater;
        $this->productUpdater = $productUpdater;
        $this->format         = $format; // TODO: useless?
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
            // TODO: use the builder for this kind of stuff, legacy and abusive manager uses,
            // cf rule action validation too
            $product = $this->manager->createProduct();
            $identifierValue = $this->manager->createProductValue();
            $identifierValue->setAttribute($this->repository->getIdentifierAttribute());
            $identifierValue->setData($identifier);
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
            if (in_array($field, ['enabled', 'categories', 'groups', 'family'])) {
                $this->productUpdater->set($product, $field, $values, []);
            } else {
                foreach ($values as $value) {
                    $options = ['locale' => $value['locale'], 'scope' => $value['scope']];
                    // TODO: use registry and not updater?
                    $this->productUpdater->set($product, $field, $value['data'], $options);
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
