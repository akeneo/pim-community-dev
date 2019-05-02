<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Sets a media data in a product.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaAttributeSetter extends AbstractAttributeSetter
{
    /** @var FileStorerInterface */
    protected $storer;

    /** @var FileInfoRepositoryInterface */
    protected $repository;

    /** @var FilesystemProvider */
    private $filesystemProvider;

    public function __construct(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        FileStorerInterface $storer,
        FileInfoRepositoryInterface $repository,
        FilesystemProvider $filesystemProvider,
        array $supportedTypes
    ) {
        parent::__construct($entityWithValuesBuilder);

        $this->storer = $storer;
        $this->repository = $repository;
        $this->filesystemProvider = $filesystemProvider;
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format :  "/absolute/file/path/filename.extension"
     */
    public function setAttributeData(
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ) {
        $options = $this->resolver->resolve($options);
        $this->checkData($attribute, $data);

        if (null === $data) {
            $file = null;
        } elseif (null === $file = $this->repository->findOneByIdentifier($data)) {
            $file = $this->storeFile($attribute, $data);
        }

        $this->entityWithValuesBuilder->addOrReplaceValue(
            $entityWithValues,
            $attribute,
            $options['locale'],
            $options['scope'],
            null !== $file ? $file->getKey() : null
        );
    }

    /**
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (null !== $data && !is_string($data)) {
            throw InvalidPropertyTypeException::stringExpected($attribute->getCode(), static::class, $data);
        }
    }

    /**
     * TODO: inform the user that this could take some time.
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @throws InvalidPropertyException If an invalid filePath is provided
     * @return FileInfoInterface|null
     */
    protected function storeFile(AttributeInterface $attribute, $data): FileInfoInterface
    {
        if (null === $data) {
            return null;
        }

        $rawFile = new \SplFileInfo($data);

        $filesystem = $this->filesystemProvider->getFilesystem('pefTmpStorage');
        if (!$filesystem->has($data)) {
            throw InvalidPropertyException::validPathExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        $file = $this->storer->store($rawFile, FileStorage::CATALOG_STORAGE_ALIAS);

        return $file;
    }
}
