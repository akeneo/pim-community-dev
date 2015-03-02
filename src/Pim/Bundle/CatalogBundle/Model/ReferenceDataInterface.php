<?php

namespace Pim\Bundle\CatalogBundle\Model;

interface ReferenceDataInterface
{
    public function getIdentifier();

    public function getIdentifierProperties();

    public function getType();

    // TODO-CR: put this in another interface ?
    public function getSortOrder();
}
