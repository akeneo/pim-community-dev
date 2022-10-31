<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\StandardFormat;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\Query\GetUserIntentsFromStandardFormat;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\UserIntentFactoryRegistry;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConvertStandardFormatIntoUserIntentsHandler
{
    public function __construct(private UserIntentFactoryRegistry $userIntentFactoryRegistry)
    {
    }

    /**
     * @return UserIntent[]
     */
    public function __invoke(GetUserIntentsFromStandardFormat $getUserIntentsFromStandardFormat): array
    {
        $standardFormat = $getUserIntentsFromStandardFormat->standardFormat();

        $userIntents = [];
        foreach ($standardFormat as $fieldName => $data) {
            $result = $this->userIntentFactoryRegistry->fromStandardFormatField($fieldName, $data);
            $userIntents = \array_merge($userIntents, $result);
        }

        return \array_filter($userIntents);
    }
}
