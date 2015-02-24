<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\Denormalization;

use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Pim\Bundle\BaseConnectorBundle\Reader\File\Formater\CsvFormater;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
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

    /** @var string */
    protected $format;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository   repository to search the object in
     * @param DenormalizerInterface                 $denormalizer denormalizer used to transform array to object
     * @param ValidatorInterface                    $validator    validator of the object
     * @param ObjectDetacherInterface               $detacher     detacher to remove it from UOW when skip
     * @param ProductManager                        $manager      product manager
     * @param CsvFormater                           $csvFormater      product manager
     * @param string                                $class        class of the object to instanciate in case if need
     * @param string                                $format       format use to denormalize
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator,
        ObjectDetacherInterface $detacher,
        ProductManager $manager,
        CsvFormater $csvFormater,
        $class,
        $format
    ) {
        parent::__construct($repository, $denormalizer, $validator, $detacher, $class);

        $this->manager     = $manager;
        $this->csvFormater = $csvFormater;
        $this->format      = $format;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $identifier = $item[$this->repository->getIdentifierProperties()[0]];
        $product    = $this->findOrCreateProduct($identifier);

        $product = $this->denormalizer->denormalize($item, $this->class, $this->format, ['entity' => $product]);

        return $product;
    }

    public function findOrCreateProduct($identifier)
    {
        $product = $this->repository->findOneByIdentifier($identifier);

        if (false === $product) {
            $product = $this->manager->createProduct();
        }

        return $product;
    }
}
