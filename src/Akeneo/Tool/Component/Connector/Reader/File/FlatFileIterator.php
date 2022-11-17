<?php

namespace Akeneo\Tool\Component\Connector\Reader\File;

use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Common\Exception\UnsupportedTypeException;
use OpenSpout\Reader\ReaderInterface;
use OpenSpout\Reader\RowIteratorInterface;
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
    protected string $type;
    protected string $filePath;
    protected ?ReaderInterface $reader = null;
    protected \SplFileInfo $fileInfo;
    protected ?string $archivePath = null;
    protected RowIteratorInterface $rows;
    protected array $headers;

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
        if ('application/zip' === $mimeType && SpoutReaderFactory::XLSX !== $this->fileInfo->getExtension()) {
            $this->extractZipArchive();
        }

        $this->reader = SpoutReaderFactory::create($type, $options['reader_options'] ?? []);

        try {
            $this->reader->open($this->filePath);
        } catch (IOException) {
            throw new \RuntimeException('File is not readable.');
        }

        $this->reader->getSheetIterator()->rewind();

        $sheet = $this->reader->getSheetIterator()->current();
        $sheet->getRowIterator()->rewind();

        $headers = $sheet->getRowIterator()->current();
        $this->headers = $headers ? $headers->toArray() : [];
        $this->rows = $sheet->getRowIterator();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->rows->rewind();
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidItemException
     */
    public function current(): mixed
    {
        $data = $this->rows->current();

        if (!$this->valid() || empty($data)) {
            $this->rewind();

            return null;
        }

        if ($data instanceof Row) {
            return $data->toArray();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function next(): void
    {
        $this->rows->next();
    }

    /**
     * {@inheritdoc}
     */
    public function key(): string|int|bool|null|float
    {
        return $this->rows->key();
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
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
}
