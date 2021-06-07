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

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Query\Locale\FindActivatedLocalesInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindActivatedLocales implements FindActivatedLocalesInterface
{
    /** @var string[] */
    private array $activatedLocales = [
        'en_US',
        'fr_FR'
    ];

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return $this->activatedLocales;
    }
}
