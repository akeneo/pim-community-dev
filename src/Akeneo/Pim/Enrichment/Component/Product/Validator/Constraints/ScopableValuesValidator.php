<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates the existence of channels used in product values.
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopableValuesValidator extends ConstraintValidator
{
    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var null|array */
    private $indexedChannelCodes = null;

    public function __construct(ChannelRepositoryInterface $channelRepository)
    {
        $this->channelRepository = $channelRepository;
    }

    public function validate($values, Constraint $constraint)
    {
        if (!$constraint instanceof ScopableValues) {
            throw new UnexpectedTypeException($constraint, AttributeOptionsExist::class);
        }

        if (!($values instanceof WriteValueCollection)) {
            return;
        }

        $indexedChannelCodes = $this->getIndexedChannelCodes();

        /** @var ValueInterface $value */
        foreach ($values->getIterator() as $value) {
            $channelCode = $value->getScopeCode();
            if ($channelCode === null) {
                continue;
            }

            if (!array_key_exists($channelCode, $indexedChannelCodes)) {
                $this->context->buildViolation($constraint->unknownScopeMessage, [
                    '%attribute_code%' => $value->getAttributeCode(),
                    '%channel%' => $channelCode,
                ])->addViolation();
            }
        }
    }

    private function getIndexedChannelCodes(): array
    {
        if ($this->indexedChannelCodes === null) {
            $channels = $this->channelRepository->findAll();

            foreach ($channels as $channel) {
                $this->indexedChannelCodes[$channel->getCode()] = 1;
            }
        }

        return $this->indexedChannelCodes;
    }
}
