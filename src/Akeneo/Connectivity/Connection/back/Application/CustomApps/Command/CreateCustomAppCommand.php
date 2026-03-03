<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\CustomApps\Command;

use Akeneo\Connectivity\Connection\Application\CustomApps\Validation\IsCustomAppsNumberLimitReached;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

#[IsCustomAppsNumberLimitReached]
final class CreateCustomAppCommand
{
    private const MESSAGE_PREFIX = 'akeneo_connectivity.connection.connect.custom_apps.create_modal.errors.';
    public function __construct(
        #[Assert\NotBlank(message: self::MESSAGE_PREFIX . 'client_id.not_blank')]
        #[Assert\Length(max: 36, maxMessage: self::MESSAGE_PREFIX . 'client_id.max_length')]
        public readonly string $clientId,
        #[Assert\NotBlank(message: self::MESSAGE_PREFIX . 'name.not_blank')]
        #[Assert\Length(
            min: 3,
            max: 255,
            minMessage: self::MESSAGE_PREFIX . 'name.min_length',
            maxMessage: self::MESSAGE_PREFIX . 'name.max_length',
        )]
        public readonly string $name,
        #[Assert\NotBlank(message: self::MESSAGE_PREFIX . 'activate_url.not_blank')]
        #[Assert\Length(max: 255, maxMessage: self::MESSAGE_PREFIX . 'activate_url.max_length')]
        #[Assert\Url(message: self::MESSAGE_PREFIX . 'activate_url.must_be_url')]
        public readonly string $activateUrl,
        #[Assert\NotBlank(message: self::MESSAGE_PREFIX . 'callback_url.not_blank')]
        #[Assert\Length(max: 255, maxMessage: self::MESSAGE_PREFIX . 'callback_url.max_length')]
        #[Assert\Url(message: self::MESSAGE_PREFIX . 'callback_url.must_be_url')]
        public readonly string $callbackUrl,
        public readonly int $userId,
    ) {
    }
}
