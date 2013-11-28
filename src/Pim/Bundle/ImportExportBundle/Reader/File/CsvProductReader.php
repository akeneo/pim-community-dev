<?php

namespace Pim\Bundle\ImportExportBundle\Reader\File;

use Doctrine\ORM\EntityManager;

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
class CsvProductReader extends CsvReader
{
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
                $data[$code] = dirname($this->file) . '/' . $value;
            }
        }

        return $data;
    }
}
