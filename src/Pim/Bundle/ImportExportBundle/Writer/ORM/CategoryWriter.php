<?php

namespace Pim\Bundle\ImportExportBundle\Writer\ORM;

/**
 * Category writer using ORM method
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryWriter extends Writer
{
    /**
     *{@inheritdoc}
     */
    protected function postWrite()
    {
        $this->em->clear('Oro\\Bundle\\SearchBundle\\Entity\\Item');
        $this->em->clear('Oro\\Bundle\\SearchBundle\\Entity\\IndexText');
    }
}
