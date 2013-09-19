<?php

namespace Oro\Bundle\ImportExportBundle\Context;

interface ContextAwareInterface
{
    /**
     * @param ContextInterface $importExportContext
     */
    public function setImportExportContext(ContextInterface $importExportContext);
}
