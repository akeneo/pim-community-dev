<?php declare(strict_types=1);

namespace Pim\Component\Catalog\VolumeLimits\Application;

use Pim\Component\Catalog\VolumeLimits\Model\Query\AttributesPerFamily;

final class GetVolumes
{
    private $attributesPerFamily;

    public function __construct(AttributesPerFamily $attributesPerFamily, array $limits = [])
    {
        $this->attributesPerFamily = $attributesPerFamily;
        $this->limits = $limits;
    }

    public function __invoke(): iterable
    {
        $attributesPerFamily = ($this->attributesPerFamily)();

        return [
            'attributes_per_family' => [
                'value' => $attributesPerFamily,
                'limit_reached' => $attributesPerFamily > ($this->limits['attributes_per_family'] ?? INF),
                'type' => 'mean_max',
            ],
        ];
    }
}
