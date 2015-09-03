<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Flat\ProductValue;

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Pim\Component\Catalog\FileStorage;

/**
 * Denormalize a product media
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileDenormalizer extends AbstractValueDenormalizer
{
    /** @var FileStorerInterface */
    protected $storer;

    /** @var FileInfoRepositoryInterface */
    protected $repository;

    /**
     * @param array                       $supportedTypes
     * @param FileInfoRepositoryInterface $repository
     * @param FileStorerInterface  $storer
     */
    public function __construct(
        array $supportedTypes,
        FileInfoRepositoryInterface $repository,
        FileStorerInterface $storer
    ) {
        parent::__construct($supportedTypes);

        $this->repository = $repository;
        $this->storer     = $storer;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (null === $data || '' === $data) {
            return null;
        }

        if (is_file($data)) {
            return $this->storer->store(new \SplFileInfo($data), FileStorage::CATALOG_STORAGE_ALIAS);
        }

        return $this->repository->findOneByIdentifier($data);
    }
}
