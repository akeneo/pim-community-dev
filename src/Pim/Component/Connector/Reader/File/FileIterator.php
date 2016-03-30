<?php

namespace Pim\Component\Connector\Reader\File;

use Akeneo\Component\Batch\Item\InvalidItemException;
use Box\Spout\Common\Type;
use Box\Spout\Reader\IteratorInterface;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Reader\ReaderInterface;
use Pim\Component\Connector\Exception\FileIteratorException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * File iterator
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileIterator implements FileIteratorInterface
{
    /** @var string */
    protected $type;

    /** @var ReaderInterface */
    protected $reader;

    /** @var string */
    protected $filePath;

    /** @var \SplFileInfo */
    protected $fileInfo;

    /** @var string */
    protected $archivePath;

    /** @var IteratorInterface */
    protected $rows;

    /** @var array */
    protected $headers;

    /**
     * @param string $type
     */
    public function __construct($type)
    {
        $this->type   = $type;
        $this->reader = ReaderFactory::create($type);
    }

    /**
     * {@inheritdoc}
     *
     * @throws FileNotFoundException
     */
    public function rewind()
    {
        if (!$this->fileInfo->isFile()) {
            throw new FileNotFoundException(sprintf('File "%s" could not be found', $this->filePath));
        }

        $mimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->filePath);
        if ('application/zip' === $mimeType && Type::XLSX !== $this->fileInfo->getExtension()) {
            $this->extractZipArchive();
        }

        $this->reader->open($this->filePath);
        $this->reader->getSheetIterator()->rewind();
        $sheet = $this->reader->getSheetIterator()->current();
        $sheet->getRowIterator()->rewind();

        $this->headers = $sheet->getRowIterator()->current();
        $this->rows    = $sheet->getRowIterator();
    }

    /**
     * {@inheritdoc}
     *
     * @throws FileIteratorException
     */
    public function current()
    {
        if (null === $this->rows) {
            throw new FileIteratorException();
        }

        $data = $this->rows->current();

        if (!$this->valid() || null === $data || empty($data)) {
            $this->reset();

            return null;
        }

        if (count($this->headers) < count($data)) {
            throw new InvalidItemException(
                'pim_connector.steps.file_reader.invalid_item_columns_count',
                $data,
                [
                    '%totalColumnsCount%' => count($this->headers),
                    '%itemColumnsCount%'  => count($data),
                    '%filePath%'          => $this->filePath,
                    '%lineno%'            => $this->rows->key()
                ]
            );
        } elseif (count($this->headers) > count($data)) {
            $missingValuesCount = count($this->headers) - count($data);
            $missingValues = array_fill(0, $missingValuesCount, '');
            $data = array_merge($data, $missingValues);
        }

        $data = array_combine($this->headers, $data);

        return $data;
    }

    /**
     * {@inheritdoc}
     *
     * @throws FileIteratorException
     */
    public function next()
    {
        if (null === $this->rows) {
            throw new FileIteratorException();
        }

        $this->rows->next();
    }

    /**
     * {@inheritdoc}
     *
     * @throws FileIteratorException
     */
    public function key()
    {
        if (null === $this->rows) {
            throw new FileIteratorException();
        }

        return $this->rows->key();
    }

    /**
     * {@inheritdoc}
     *
     * @throws FileIteratorException
     */
    public function valid()
    {
        if (null === $this->rows) {
            throw new FileIteratorException();
        }

        return $this->rows->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function isInitialized()
    {
        return null !== $this->rows;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->rows        = null;
        $this->headers     = null;
        $this->fileInfo    = null;
        $this->filePath    = null;
        $this->archivePath = null;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function setReaderOptions(array $options = [])
    {
        foreach ($options as $name => $option) {
            $setter = 'set' . ucfirst($name);
            if (method_exists($this->reader, $setter)) {
                $this->reader->$setter($option);
            } else {
                $message = sprintf('Option "%s" does not exist in reader "%s"', $setter, get_class($this->reader));
                throw new \LogicException($message);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
        $this->fileInfo = new \SplFileInfo($filePath);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDirectoryPath()
    {
        if (null == $this->archivePath) {
            return $this->fileInfo->getPath();
        }

        return $this->archivePath;
    }

    /**
     * {@inheritdoc}
     */
    public function __destruct()
    {
        $this->reader->close();
        $this->reset();

        if ($this->archivePath) {
            $fileSystem = new Filesystem();
            $fileSystem->remove($this->archivePath);
        }
    }

    /**
     * Extract the zip archive to be imported
     *
     * @throws \RuntimeException When archive cannot be opened or extracted or does not contain exactly one file file
     */
    protected function extractZipArchive()
    {
        $archive = new \ZipArchive();

        $status = $archive->open($this->filePath);
        if (true !== $status) {
            throw new \RuntimeException(sprintf('Error "%d" occurred while opening the zip archive.', $status));
        }

        $path = $this->fileInfo->getPath();
        $filename = $this->fileInfo->getBasename('.' . $this->fileInfo->getExtension());
        $targetDir = sprintf('%s/%s', $path, $filename);
        if (!$archive->extractTo($targetDir)) {
            throw new \RuntimeException('Error occurred while extracting the zip archive.');
        }

        $archive->close();
        $this->archivePath = $targetDir;

        $finder = new Finder();
        $files = $finder->in($targetDir)->name('/\.' . $this->type . '$/i');

        $count = $files->count();
        if (1 !== $count) {
            throw new \RuntimeException(
                sprintf(
                    'Expecting the root directory of the archive to contain exactly 1 file file, found %d',
                    $count
                )
            );
        }

        $this->filePath = $files->getIterator()->getRealpath();
    }
}
