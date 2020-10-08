<?php

namespace Akeneo\Tool\Component\Connector\Reader\File;

use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Common\Type;
use Box\Spout\Reader\IteratorInterface;
use Box\Spout\Reader\ReaderFactory;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Use Spout library to iterate on each rows of file.
 *
 * Iterates over XLSX & CSV files.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatFileIterator implements FileIteratorInterface
{
    /** @var string */
    protected $type;

    /** @var string */
    protected $filePath;

    /** @var FileReaderInterface */
    protected $reader;

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
     * @param string $filePath
     * @param array  $options
     *
     * @throws UnsupportedTypeException
     * @throws FileNotFoundException
     */
    public function __construct($type, $filePath, array $options = [])
    {
        $this->type = $type;
        $this->filePath = $filePath;
        $this->fileInfo = new \SplFileInfo($filePath);

        if (!$this->fileInfo->isFile()) {
            throw new FileNotFoundException(sprintf('File "%s" could not be found', $this->filePath));
        }

        $mimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->filePath);
        if ('application/zip' === $mimeType && Type::XLSX !== $this->fileInfo->getExtension()) {
            $this->extractZipArchive();
        }

        $this->reader = ReaderFactory::create($type);
        if (isset($options['reader_options'])) {
            $this->setReaderOptions($options['reader_options']);
        }
        $this->reader->open($this->filePath);
        $this->reader->getSheetIterator()->rewind();

        $sheet = $this->reader->getSheetIterator()->current();
        $sheet->getRowIterator()->rewind();

        $this->headers = $sheet->getRowIterator()->current();
        $this->rows = $sheet->getRowIterator();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->rows->rewind();
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidItemException
     */
    public function current()
    {
        $data = $this->rows->current();

        if (!$this->valid() || null === $data || empty($data)) {
            $this->rewind();

            return null;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->rows->next();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->rows->key();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->rows->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function getDirectoryPath()
    {
        if (null === $this->archivePath) {
            return $this->fileInfo->getPath();
        }

        return $this->archivePath;
    }

    /**
     * Close reader and remove folder created when archive has been extracted
     */
    public function __destruct()
    {
        if (null !== $this->reader) {
            $this->reader->close();
        }

        if (null !== $this->archivePath) {
            $fileSystem = new Filesystem();
            $fileSystem->remove($this->archivePath);
            $this->archivePath = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return $this->headers;
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

        $filesIterator = $files->getIterator();
        $filesIterator->rewind();

        $this->filePath = $filesIterator->current()->getPathname();
    }

    /**
     * Add options to Spout reader
     *
     * @param array $readerOptions
     *
     * @throws \InvalidArgumentException
     */
    protected function setReaderOptions(array $readerOptions = [])
    {
        foreach ($readerOptions as $name => $option) {
            $setter = 'set' . ucfirst($name);
            if (method_exists($this->reader, $setter)) {
                $this->reader->$setter($option);
            } else {
                $message = sprintf('Option "%s" does not exist in reader "%s"', $setter, get_class($this->reader));
                throw new \InvalidArgumentException($message);
            }
        }
    }
}
