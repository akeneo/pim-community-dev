<?php

namespace Pim\Component\Catalog\Comparator\Attribute;

use Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Pim\Component\Catalog\Comparator\ComparatorInterface;

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
        $this->types      = $types;
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
        $default   = ['locale' => null, 'scope' => null, 'data' => ['filePath' => null]];
        $originals = array_merge($default, $originals);

        if (!isset($data['data']['filePath']) && !isset($originals['data']['filePath']) ||
            $this->filesMatch($data, $originals)
        ) {
            return null;
        }

        // compare a local file and a stored file (can happen during an import for instance)
        if (isset($originals['data']['filePath']) &&
            isset($data['data']['filePath']) &&
            is_file($data['data']['filePath'])
        ) {
            $originalFile = $this->repository->findOneByIdentifier($originals['data']['filePath']);
            if (null !== $originalFile && $originalFile->getHash() === $this->getHashFile($data['data']['filePath'])) {
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

    /**
     * Check if files match by their hash or path
     *
     * @param mixed  $data
     * @param mixed  $originals
     *
     * @return bool
     */
    protected function filesMatch($data, $originals)
    {
        return
            (
                isset($data['data']['filePath']) &&
                isset($originals['data']['filePath']) &&
                $data['data']['filePath'] === $originals['data']['filePath']
            ) || (
                isset($data['data']['hash']) &&
                isset($originals['data']['hash']) &&
                $data['data']['hash'] === $originals['data']['hash']
            );
    }
}
