<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class RuleTemplate
{
    /** @var array  */
    private $content;

    public function __construct(array $content)
    {
        $this->content = $content;
    }

    public function createFromNormalized(array $content)
    {
        return new self($content);
    }

    public function getContent(): array
    {
        return $this->content;
    }

    public function getConditions(): array
    {
        return $this->content['conditions'];
    }

    public function getActions(): array
    {
        return $this->content['actions'];
    }
}
