<?php

namespace Pim\Bundle\SearchBundle\Engine;

use Oro\Bundle\SearchBundle\Engine\ObjectMapper as OroObjectMapper;

/**
 * Override object mapper class to remove tag/email as searchable entities
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ObjectMapper extends OroObjectMapper
{
    /**
     * {@inheritdoc}
     */
    public function getEntitiesListAliases()
    {
        $entities = parent::getEntitiesListAliases();

        if (isset($entities['Oro\Bundle\TagBundle\Entity\Tag'])) {
            unset($entities['Oro\Bundle\TagBundle\Entity\Tag']);
        }
        if (isset($entities['Oro\Bundle\EmailBundle\Entity\Email'])) {
            unset($entities['Oro\Bundle\EmailBundle\Entity\Email']);
        }

        return $entities;
    }

    /**
     * {@inheritdoc}
     *
     * TODO enable the search indexing when using mongodb
     */
    public function getEntities()
    {
        $driver = $this->container->getParameter('pim_catalog.storage_driver');
        if ('doctrine/mongodb-odm' === $driver) {
            unset($this->mappingConfig['Pim\Bundle\CatalogBundle\Model\Product']);
        }

        return array_keys($this->mappingConfig);
    }
}
