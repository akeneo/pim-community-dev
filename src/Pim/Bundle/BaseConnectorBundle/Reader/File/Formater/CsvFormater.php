<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\File\Formater;

use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Csv formater
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvFormater
{
    const ARRAY_SEPARATOR            = ',';
    const FIELD_SEPARATOR            = '-';
    const GROUP_ASSOCIATION_SUFFIX   = '-groups';
    const PRODUCT_ASSOCIATION_SUFFIX = '-products';

    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var string */
    protected $associationTypeClass;

    /**
     * Constructor
     *
     * @param ManagerRegistry $managerRegistry
     * @param string          $associationTypeClass
     */
    public function __construct(ManagerRegistry $managerRegistry, $associationTypeClass)
    {
        $this->managerRegistry      = $managerRegistry;
        $this->associationTypeClass = $associationTypeClass;
    }

    public function getAssociationFieldNames()
    {
        $fieldNames = [];
        $associationTypes = $this->managerRegistry->getRepository($this->associationTypeClass)->findAll();
        foreach ($associationTypes as $associationType) {
            $fieldNames[] = $associationType->getCode() . self::GROUP_ASSOCIATION_SUFFIX;
            $fieldNames[] = $associationType->getCode() . self::PRODUCT_ASSOCIATION_SUFFIX;
        }

        return $fieldNames;
    }
}
