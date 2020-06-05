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

class ProductDestination
{
    public $field;
    public $scope;
    public $locale;
    public $currency;
    public $unit;

    public function __construct(array $data)
    {
        $this->field = $data['field'] ?? null;
        $this->scope = $data['scope'] ?? null;
        $this->locale = $data['locale'] ?? null;
        $this->currency = $data['currency'] ?? null;
        $this->unit = $data['unit'] ?? null;
    }
}
