<?php declare(strict_types=1);

namespace Pim\Component\Catalog\VolumeLimits\Model\Query;

interface AttributesPerFamily
{
    public function __invoke(): array;
}
