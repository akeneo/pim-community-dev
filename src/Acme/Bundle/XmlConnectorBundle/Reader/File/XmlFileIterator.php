<?php

namespace Acme\Bundle\XmlConnectorBundle\Reader\File;

use Box\Spout\Reader\ReaderFactory;
use Pim\Component\Connector\Reader\File\FileIteratorInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * A simple XmlFileIterator
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class XmlFileIterator implements FileIteratorInterface
{
    /** @var string **/
    protected $type;

    /** @var string **/
    protected $filePath;

    /** @var \SplFileInfo **/
    protected $fileInfo;

    /** @var \SimpleXMLIterator */
    protected $xmlFileIterator;

    /**
     * {@inheritdoc}
     */
    public function __construct($type, $filePath, array $options = [])
    {
        $this->type     = $type;
        $this->filePath = $filePath;
        $this->fileInfo = new \SplFileInfo($filePath);

        if (!$this->fileInfo->isFile()) {
            throw new FileNotFoundException(sprintf('File "%s" could not be found', $this->filePath));
        }

        $this->xmlFileIterator = simplexml_load_file($filePath, 'SimpleXMLIterator');
        $this->xmlFileIterator->rewind();
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
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        $headers = [];

        $xmlReader = simplexml_load_file($this->filePath, 'SimpleXMLIterator');
        $xmlReader->rewind();

        if ($data = $xmlReader->current()) {
            foreach ($data->attributes() as $attributeName => $attributeValue) {
                if (!in_array($attributeName, $headers)) {
                    $headers[] = $attributeName;
                }
            }
            $xmlReader->next();
        }

        return $headers;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $elem = $this->xmlFileIterator->current();

        return $this->xmlElementToFlat($elem);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->xmlFileIterator->next();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->xmlFileIterator->key();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->xmlFileIterator->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->xmlFileIterator->rewind();
    }

    /**
     * @param \SimpleXMLIterator $elem
     *
     * @return array
     */
    protected function xmlElementToFlat($elem)
    {
        $flatElem = [];
        if (null !== $elem) {
            foreach ($elem->attributes() as $property => $value) {
                $flatElem[] = (string) $value;
            }
        }

        return $flatElem;
    }
}
