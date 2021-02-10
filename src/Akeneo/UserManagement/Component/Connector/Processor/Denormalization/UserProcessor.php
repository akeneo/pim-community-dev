<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Connector\Processor\Denormalization;

use Akeneo\Tool\Component\Connector\Processor\Denormalization\Processor;
use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserProcessor extends Processor
{
    /**
     * {@inheritdoc}
     */
    public function process($item): ?UserInterface
    {
        if ($item['password'] ?? null) {
            $this->skipItemWithMessage($item, 'Passwords cannot be imported via flat files');
        }

        $itemIdentifier = $this->getItemIdentifier($this->repository, $item);
        $user = $this->repository->findOneByIdentifier($itemIdentifier);
        if (null === $user) {
            $item['password'] = \uniqid('tmp_pwd');
        }

        return parent::process($item);
    }
}
