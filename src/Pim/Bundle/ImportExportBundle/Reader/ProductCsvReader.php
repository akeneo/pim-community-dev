<?php

namespace Pim\Bundle\ImportExportBundle\Reader;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;

/**
 * Product csv reader
 *
 * This specialized csv reader exists because, as the product has bulk inserted,
 * we cannot rely on the UniqueValueValidator which rely on data present inside the database.
 * Its second purpose is to replace relative media path to absolute path, in order for later
 * process to know where to find the files.
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCsvReader extends CsvReader
{
    /** @var array Unique attribute value data grouped by attribute codes */
    protected $uniqueValues = array();

    /** @var array Media attribute codes */
    protected $mediaAttributes = array();

    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $repository = $entityManager->getRepository('PimCatalogBundle:ProductAttribute');
        foreach ($repository->findUniqueAttributeCodes() as $code) {
            $this->uniqueValues[$code] = array();
        }
        $this->mediaAttributes = $repository->findMediaAttributeCodes();
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $data = parent::read();

        if (!is_array($data)) {
            return $data;
        }

        $this->assertValueUniqueness($data);

        return $this->transformMediaPathToAbsolute($data);
    }

    protected function assertValueUniqueness(array $data)
    {
        foreach ($data as $code => $value) {
            if (array_key_exists($code, $this->uniqueValues)) {
                if (in_array($value, $this->uniqueValues[$code])) {
                    throw new InvalidItemException(
                        sprintf(
                            'The "%s" attribute is unique, the value "%s" was already read ' .
                            'in this file in %s:%s.',
                            $code,
                            $value,
                            $this->csv->getRealPath(),
                            $this->csv->key()
                        ),
                        $data
                    );
                }
                $this->uniqueValues[$code][] = $value;
            }
        }
    }

    /**
     * Prepend the current imported file directory to the media attribute values
     *
     * @param array $data
     *
     * @return array
     */
    protected function transformMediaPathToAbsolute(array $data)
    {
        foreach ($data as $code => $value) {
            if (empty($value)) {
                continue;
            }

            $pos = strpos($code, '-');
            $attributeCode = false !== $pos ? substr($code, 0, $pos) : $code;

            if (in_array($attributeCode, $this->mediaAttributes)) {
                $filePath = dirname($this->filePath) . '/' . $value;
                if (!is_file($filePath)) {
                    throw new InvalidItemException(
                        sprintf(
                            'Could not find the file "%s" in %s:%s',
                            $filePath,
                            $this->csv->getRealPath(),
                            $this->csv->key()
                        ),
                        $data
                    );
                }
                $data[$code] = $filePath;
            }
        }

        return $data;
    }
}
