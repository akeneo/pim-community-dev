<?php

namespace Pim\Component\Catalog\Comparator\Attribute;

use Akeneo\Component\FileStorage\Repository\FileRepositoryInterface;
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
    /** @var  FileRepositoryInterface */
    protected $repository;

    /** @var array */
    protected $types;

    /**
     * @param array                   $types
     * @param FileRepositoryInterface $repository
     */
    public function __construct(array $types, FileRepositoryInterface $repository)
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
        $default = ['locale' => null, 'scope' => null, 'data' => ['filePath' => null]];
        $originals = array_merge($default, $originals);

        if (null !== $originals['data']['filePath']) {
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
}
