<?php

namespace Pim\Component\Catalog\Denormalizer\Structured\ProductValue;

use Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface;

/**
 * Product file denormalizer used for following attribute type:
 * - pim_catalog_file
 * - pim_catalog_image
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileDenormalizer extends AbstractValueDenormalizer
{
    /** @var FileInfoRepositoryInterface */
    protected $repository;

    /**
     * @param array                       $supportedTypes
     * @param FileInfoRepositoryInterface $repository
     */
    public function __construct(array $supportedTypes, FileInfoRepositoryInterface $repository)
    {
        parent::__construct($supportedTypes);

        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (empty($data)) {
            return null;
        }

        return $this->repository->findOneByIdentifier($data);
    }
}
