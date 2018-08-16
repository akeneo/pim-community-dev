<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Comparator\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;

/**
 * Comparator which calculate change set for files
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileComparator implements ComparatorInterface
{
    /** @var  FileInfoRepositoryInterface */
    protected $repository;

    /** @var array */
    protected $types;

    /**
     * @param array                   $types
     * @param FileInfoRepositoryInterface $repository
     */
    public function __construct(array $types, FileInfoRepositoryInterface $repository)
    {
        $this->types = $types;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return in_array($type, $this->types);
    }

    /**
     * {@inheritdoc}
     */
    public function compare($data, $originals)
    {
        $default = ['locale' => null, 'scope' => null, 'data' => null];
        $originals = array_merge($default, $originals);

        if (!isset($data['data']) && !isset($originals['data'])) {
            return null;
        }

        if (isset($data['data']) && isset($originals['data'])) {
            $originalFile = $this->repository->findOneByIdentifier($originals['data']);

            // compare a local file and a stored file (can happen during an import for instance)
            if (is_file($data['data']) &&
                null !== $originalFile &&
                $originalFile->getHash() === $this->getHashFile($data['data'])
            ) {
                return null;
            }

            $file = $this->repository->findOneByIdentifier($data['data']);

            if (null !== $file &&
                null !== $originalFile &&
                $file->getHash() === $originalFile->getHash()
            ) {
                return null;
            }
        }

        return $data;
    }

    /**
     * @param string $filePath
     *
     * @return null|string
     */
    protected function getHashFile($filePath = null)
    {
        return null !== $filePath ? sha1_file($filePath) : null;
    }
}
