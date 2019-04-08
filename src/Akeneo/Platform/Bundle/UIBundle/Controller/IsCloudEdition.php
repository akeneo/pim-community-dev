<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Controller;

use Akeneo\Tool\Component\Localization\Provider\LocaleProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class IsCloudEdition
{
    /** @var bool */
    private $isCloud;

    public function __construct(bool $isCloud)
    {
        $this->isCloud = $isCloud;
    }

    public function __invoke(): JsonResponse
    {
        return new JsonResponse([
            'isCloudEdition' => $this->isCloud
        ]);
    }
}
