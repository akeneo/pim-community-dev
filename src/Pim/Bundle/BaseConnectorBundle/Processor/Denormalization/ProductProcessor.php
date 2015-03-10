<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\Denormalization;

use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Pim\Bundle\BaseConnectorBundle\Reader\File\Formater\CsvFormater;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
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
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator,
        ObjectDetacherInterface $detacher,
        ProductManager $manager,
        CsvFormater $csvFormater,
        ProductUpdaterInterface $productUpdater,
        $class,
        $format
    ) {
        parent::__construct($repository, $denormalizer, $validator, $detacher, $class);

        $this->manager        = $manager;
        $this->csvFormater    = $csvFormater;
        $this->productUpdater = $productUpdater;
        $this->format         = $format;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $item = $this->csvFormater->convertToStructured($item);
        $identifier = $item[$this->repository->getIdentifierProperties()[0]][0]['data'];
        $product    = $this->findOrCreateProduct($identifier);

        unset($item[$this->repository->getIdentifierProperties()[0]]);
        unset($item['associations']);
        unset($item['family']);
        unset($item['categories']);
        unset($item['enabled']);

        foreach ($item as $field => $values) {
            foreach ($values as $value) {
                $this->productUpdater->setValue([$product], $field, $value['data'], $value['locale'], $value['scope']);
            }
        }

        return $product;
    }

    public function findOrCreateProduct($identifier)
    {
        $product = $this->repository->findOneByIdentifier($identifier);

        if (false === $product) {
            $product = $this->manager->createProduct();
            $identifierValue = $this->manager->createProductValue();
            $identifierValue->setAttribute($this->repository->getIdentifierAttribute());
            $identifierValue->setData($identifier);
        }

        return $product;
    }
}
