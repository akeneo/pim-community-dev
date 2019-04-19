<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
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

    /**
     * @param EntityWithValuesBuilderInterface $entityWithValuesBuilder
     * @param FileStorerInterface              $storer
     * @param FileInfoRepositoryInterface      $repository
     * @param string[]                         $supportedTypes
     */
    public function __construct(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        FileStorerInterface $storer,
        FileInfoRepositoryInterface $repository,
        array $supportedTypes
    ) {
        parent::__construct($entityWithValuesBuilder);

        $this->storer = $storer;
        $this->repository = $repository;
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
        } elseif (!$this->isFileAlreadyStored($data)) {
            $file = $this->storeFile($attribute, $data);
        } else {
            $file = $this->repository->findOneByIdentifier($data);
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
     * @param string             $data
     *
     * @return FileInfoInterface
     *
     * @throws \Akeneo\Tool\Component\FileStorage\Exception\FileRemovalException
     * @throws \Akeneo\Tool\Component\FileStorage\Exception\FileTransferException
     * @throws \Exception
     */
    protected function storeFile(AttributeInterface $attribute, string $data): FileInfoInterface
    {
        $rawFile = new \SplFileInfo($data);

        if (!$rawFile->isFile()) {
            throw InvalidPropertyException::validPathExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        $file = $this->storer->store($rawFile, FileStorage::CATALOG_STORAGE_ALIAS);

        return $file;
    }

    private function isFileAlreadyStored(string $data): bool
    {
        return null !== $this->repository->findOneByIdentifier($data);
    }
}
