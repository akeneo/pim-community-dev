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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Channel;

use Akeneo\AssetManager\Domain\Query\Locale\FindActivatedLocalesInterface;
use Akeneo\Channel\API\Query\FindLocales;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class FindActivatedLocales implements FindActivatedLocalesInterface
{
    public function __construct(
        private FindLocales $findLocales
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        $activatedLocales = $this->findLocales->findAllActivated();

        return array_map(static fn ($locale) => $locale->getCode(), $activatedLocales);
    }
}
