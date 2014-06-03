<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * Provide translation capability
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface TranslatorAwareInterface
{
    /**
     * Set the translator
     *
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator);
}
