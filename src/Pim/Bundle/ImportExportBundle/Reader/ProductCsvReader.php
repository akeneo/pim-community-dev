<?php

namespace Pim\Bundle\ImportExportBundle\Reader;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\BatchBundle\Entity\StepExecution;

/**
 * Product csv reader
 *
 * This specialized csv reader exists because, as the product has bulk inserted,
 * we cannot rely on the UniqueValueValidator which rely on data present inside the database.
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCsvReader extends CsvReader
{
    /**
     * @var array Unique attribute value data grouped by attribute codes
     */
    protected $uniqueValues = array();

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
    }

    /**
     * {@inheritdoc}
     */
    public function read(StepExecution $stepExecution)
    {
        $data = parent::read($stepExecution);

        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $code => $value) {
            if (array_key_exists($code, $this->uniqueValues)) {
                if (in_array($value, $this->uniqueValues[$code])) {
                    $stepExecution->addReaderWarning(
                        get_class($this),
                        sprintf(
                            'The "%s" attribute is unique, the value "%s" was already read in this file in %s:%s.',
                            $code,
                            $value,
                            $this->csv->getRealPath(),
                            $this->csv->key()
                        ),
                        $data
                    );

                    return false;
                }
                $this->uniqueValues[$code][] = $value;
            }
        }

        return $data;
    }
}
