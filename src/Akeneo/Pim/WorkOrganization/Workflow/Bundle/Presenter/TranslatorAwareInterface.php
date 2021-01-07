<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Provide translation capability
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
interface TranslatorAwareInterface
{
    /**
     * Set the translator
     */
    public function setTranslator(TranslatorInterface $translator);
}
