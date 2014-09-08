<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * Provide translation capability
 *
 * @author Gildas Quemener <gildas@akeneo.com>
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
