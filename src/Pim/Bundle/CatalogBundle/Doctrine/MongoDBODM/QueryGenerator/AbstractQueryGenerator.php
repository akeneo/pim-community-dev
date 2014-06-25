<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\AttributeNamingUtility;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
* Abstract query generator
*/
abstract class AbstractQueryGenerator implements NormalizedDataQueryGeneratorInterface
{
    /** @var AttributeNamingUtility */
    protected $attributeNamingUtility;

    /** @var string */
    protected $entityClass;

    /** @var string */
    protected $field;

    /**
     * @param AttributeNamingUtility $attributeNamingUtility
     * @param string                 $entityClass
     * @param string                 $field
     */
    public function __construct(
        AttributeNamingUtility $attributeNamingUtility,
        $entityClass,
        $field = ''
    ) {
        $this->attributeNamingUtility = $attributeNamingUtility;
        $this->entityClass            = $entityClass;
        $this->field                  = $field;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity, $field)
    {
        return $entity instanceof $this->entityClass && $field === $this->field;
    }

    /**
     * Get possible attribute codes
     *
     * @return array
     */
    protected function getPossibleAttributeCodes(AbstractAttribute $attribute, $prefix = '')
    {
        $localeCode  = $this->attributeNamingUtility->getLocaleCode($attribute);
        $channelCode = $this->attributeNamingUtility->getChannelCode($attribute);

        $attributeCodes = [($prefix !== '' ? $prefix : '') . $attribute->getCode()];

        $attributeCodes = $this->attributeNamingUtility->appendSuffixes($attributeCodes, $localeSuffixes, '-');
        $attributeCodes = $this->attributeNamingUtility->appendSuffixes($attributeCodes, $channelSuffixes, '-');

        return $attributeCodes;
    }
}