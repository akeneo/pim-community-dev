<?php

namespace Pim\Bundle\ImportExportBundle\Exception;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * Interface for translatable exceptions
 * 
 * @author Antoine Guigan <aguigan@qimnet.com>
 */
interface TranslatableExceptionInterface
{
    public function translateMessage(TranslatorInterface $translator);
}
