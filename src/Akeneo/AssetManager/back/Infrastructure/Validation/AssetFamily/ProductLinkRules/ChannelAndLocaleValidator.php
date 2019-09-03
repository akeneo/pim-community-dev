<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;
use Akeneo\AssetManager\Domain\Query\Channel\ChannelExistsInterface;
use Akeneo\AssetManager\Domain\Query\Locale\FindActivatedLocalesByIdentifiersInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validation;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ChannelAndLocaleValidator
{
    /** @var ChannelExistsInterface */
    private $channelExists;

    /** @var FindActivatedLocalesByIdentifiersInterface */
    private $findActivatedLocalesByIdentifiers;

    public function __construct(
        ChannelExistsInterface $channelExists,
        FindActivatedLocalesByIdentifiersInterface $findActivatedLocalesByIdentifiers
    ) {
        $this->channelExists = $channelExists;
        $this->findActivatedLocalesByIdentifiers = $findActivatedLocalesByIdentifiers;
    }

    public function checkChannelExistsIfAny(?string $channelCode): ConstraintViolationListInterface
    {
        if (null === $channelCode) {
            return new ConstraintViolationList();
        }

        return $this->checkChannelExists($channelCode);
    }

    public function checkLocaleExistsIfAny(?string $localeCode): ConstraintViolationListInterface
    {
        if (null === $localeCode) {
            return new ConstraintViolationList();
        }

        return $this->checkLocaleExists($localeCode);
    }

    private function checkChannelExists(string $channelCode): ConstraintViolationListInterface
    {
        $isChannelExisting = $this->channelExists->exists(ChannelIdentifier::fromCode($channelCode));
        $validator = Validation::createValidator();

        return $validator->validate(
            $isChannelExisting,
            new Callback(function ($attributeExists, ExecutionContextInterface $context) use (
                $channelCode
            ) {
                if (!$attributeExists) {
                    $context
                        ->buildViolation(ProductLinkRulesShouldBeExecutable::CHANNEL_SHOULD_EXIST,
                            ['%channel_code%' => $channelCode]
                        )
                        ->addViolation();
                }
            }
            )
        );
    }

    private function checkLocaleExists(string $localeCode): ConstraintViolationListInterface
    {
        $activatedLocales = $this->findActivatedLocalesByIdentifiers->find(LocaleIdentifierCollection::fromNormalized([$localeCode]))
                                                                    ->normalize();
        $isLocaleExisting = in_array($localeCode, $activatedLocales);
        $validator = Validation::createValidator();

        return $validator->validate(
            $isLocaleExisting,
            new Callback(function ($attributeExists, ExecutionContextInterface $context) use (
                $localeCode
            ) {
                if (!$attributeExists) {
                    $context
                        ->buildViolation(ProductLinkRulesShouldBeExecutable::LOCALE_SHOULD_EXIST,
                            ['%locale_code%' => $localeCode]
                        )
                        ->addViolation();
                }
            }
            )
        );
    }
}
