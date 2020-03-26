<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\RuleEngineBundle\Model;

use Akeneo\Tool\Component\Localization\Model\AbstractTranslation;

class RuleDefinitionTranslation extends AbstractTranslation implements RuleDefinitionTranslationInterface
{
    /** @var string */
    private $label;

    public function setLabel(string $label): RuleDefinitionTranslationInterface
    {
        $this->label = $label;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}
