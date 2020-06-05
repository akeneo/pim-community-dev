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

namespace Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO;

final class CopyAction
{
    public $fromField;
    public $fromLocale;
    public $fromScope;

    public $toField;
    public $toLocale;
    public $toScope;

    public function __construct(array $data)
    {
        $this->fromField = $data['from_field'] ?? null;
        $this->fromLocale =$data['from_locale'] ?? null;
        $this->fromScope = $data['from_scope'] ?? null;

        $this->toField =   $data['to_field'] ?? null;
        $this->toLocale =  $data['to_locale'] ?? null;
        $this->toScope =   $data['to_scope'] ?? null;
    }
}
