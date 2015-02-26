<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\File;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\CurrencyRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Pim\Bundle\TransformBundle\Builder\FieldNameBuilder;

/**
 * Association csv reader
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvAssociationReader extends CsvReader
{
    /** @var FieldNameBuilder */
    protected $fieldNameBuilder;

    /**
     * Constructor
     *
     * @param FieldNameBuilder $fieldNameBuilder
     */
    public function __construct(FieldNameBuilder $fieldNameBuilder)
    {
        $this->fieldNameBuilder = $fieldNameBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $data = parent::read();
        if (null === $data) {
            return;
        }

        // Get association field names and add associations
        $assocFieldNames  = $this->fieldNameBuilder->getAssociationFieldNames();
        $associations = [
            'product'      => $data,
            'associations' => []
        ];
        foreach ($assocFieldNames as $assocFieldName) {
            if (isset($data[$assocFieldName])) {
                if (strlen($data[$assocFieldName]) > 0) {
                    list($assocTypeCode, $part) = explode(FieldNameBuilder::FIELD_SEPARATOR, $assocFieldName);

                    $associations['associations'][] = [
                        'associated_items'      => explode(FieldNameBuilder::ARRAY_SEPARATOR, $data[$assocFieldName]),
                        'association_type_code' => $assocTypeCode,
                        'item_type'             => $part
                    ];
                }
            }
        }

        return $associations;
    }
}
