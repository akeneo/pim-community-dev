<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Application\ReferenceEntity\EditReferenceEntity;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditReferenceEntityCommand
{
    /** @var string */
    public $identifier;

    /** @var array */
    public $labels;

    /** @var array|null */
    public $image;

    public function __construct(string $identifier, array $labels, ?array $image)
    {
        $this->identifier = $identifier;
        $this->labels = $labels;
        $this->image = $image;
    }
}
