<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Doctrine\ORM\Persisters\Entity\BasicEntityPersister;

class CompletenessPersister extends BasicEntityPersister
{
    /**
     * {@inheritdoc}
     */
    public function getInsertSQL()
    {
        return str_replace('INSERT INTO ', 'REPLACE INTO ', parent::getInsertSQL());
    }
}
