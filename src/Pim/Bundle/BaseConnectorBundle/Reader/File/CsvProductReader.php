<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\File;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\BaseConnectorBundle\Archiver\InvalidItemsCsvArchiver;

/**
 * Product csv reader
 *
 * This specialized csv reader exists because, as the product are bulk inserted,
 * we cannot rely on the UniqueValueValidator which rely on data present inside the database.
 * Its second purpose is to replace relative media path to absolute path, in order for later
 * process to know where to find the files.
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvProductReader extends CsvReader
{
    /** @var array Media attribute codes */
    protected $mediaAttributes = array();

    /**
     * Constructor
     *
     * @param InvalidItemsCsvArchiver $archiver
     * @param EntityManager           $entityManager
     * @param string                  $attributeClass
     */
    public function __construct(InvalidItemsCsvArchiver $archiver, EntityManager $entityManager, $attributeClass)
    {
        parent::__construct($archiver);

        $repository = $entityManager->getRepository($attributeClass);
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

        return $this->transformMediaPathToAbsolute($data);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function transformMediaPathToAbsolute(array $data)
    {
        foreach ($data as $code => $value) {
            $pos = strpos($code, '-');
            $attributeCode = false !== $pos ? substr($code, 0, $pos) : $code;

            if (in_array($attributeCode, $this->mediaAttributes)) {
                $data[$code] = dirname($this->filePath) . '/' . $value;
            }
        }

        return $data;
    }
}
