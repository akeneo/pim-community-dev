<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Query\Locale;


/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
interface FindActivatedLocalesInterface
{
    public function __invoke(): array;
}
