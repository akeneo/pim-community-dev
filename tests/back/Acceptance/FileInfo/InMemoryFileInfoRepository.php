<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\FileInfo;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Akeneo\Test\IntegrationTestsBundle\Assertion\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryFileInfoRepository implements FileInfoRepositoryInterface, SaverInterface
{
    /** @var Collection */
    private $fileInfoCollection;

    /** @var string */
    private $className;

    /**
     * @param array  $fileInfoCollection
     * @param string $className
     */
    public function __construct(array $fileInfoCollection, string $className)
    {
        $this->fileInfoCollection = new ArrayCollection($fileInfoCollection);
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->attributes->get($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * {@inheritdoc}
     */
    public function save($fileInfo, array $options = [])
    {
        if (!$fileInfo instanceof FileInfoInterface) {
            throw new \InvalidArgumentException('The object argument should be a file info');
        }

        $this->fileInfoCollection->set($fileInfo->getCode(), $fileInfo);
    }
}
