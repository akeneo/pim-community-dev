<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Automation\Integration\RuleEngine\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\CustomAction;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
final class CustomActionValidatorIntegration extends KernelTestCase
{
    private ValidatorInterface $validator;
    private DenormalizerInterface $chainedDenormalizer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->testKernel = static::bootKernel(['debug' => false]);
        $container = self::$container;
        $this->validator = $container->get('validator');
        $this->chainedDenormalizer = $container->get('pimee_catalog_rule.denormalizer.product_rule.chained');
    }

    public function testItValidatesACustomAction()
    {
        $customAction = new CustomAction(['type' => 'custom', 'field' => 'name']);
        $this->givenADenormalizerForTheCustomRule();

        $violations = $this->validator->validate($customAction);
        self::assertSame(0, $violations->count());
    }

    public function testItReturnsAViolationWhenCustomActionCannotBeDenormalized()
    {
        $customAction = new CustomAction(['type' => 'custom2', 'field' => 'name']);

        $violations = $this->validator->validate($customAction);
        self::assertSame(1, $violations->count());
        self::assertStringContainsString('Unknown action type', $violations->__toString());
    }

    private function givenADenormalizerForTheCustomRule(): void
    {
        $this->chainedDenormalizer->addDenormalizer(new class() implements DenormalizerInterface {
            public function supportsDenormalization($data, $type, $format = null)
            {
                return $data['type'] === 'custom';
            }
            public function denormalize($data, $type, $format = null, array $context = [])
            {
                return new \stdClass();
            }
        });
    }
}
