<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Channel;

interface GetChannelTranslations
{
    public function byLocale(string $locale): array;
}
