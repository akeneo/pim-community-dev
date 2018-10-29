<?php

namespace AkeneoTest\Pim\Structure\Integration\AssociationType\Validation;

use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AssociationTypeValidationIntegration extends TestCase
{
    public function testAssociationTypeUniqueEntity()
    {
        $associationType = $this->createAssociationType();
        $this->getUpdater()->update($associationType, ['code' => 'X_SELL']);

        $violations = $this->getValidator()->validate($associationType);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame('This value is already used.', $violation->getMessage());
        $this->assertSame('code', $violation->getPropertyPath());
    }

    public function testAssociationTypeImmutableCode()
    {
        $associationType = $this->get('pim_catalog.repository.association_type')->findOneByIdentifier('X_SELL');
        $this->getUpdater()->update($associationType, ['code' => 'XSELL']);

        $violations = $this->getValidator()->validate($associationType);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame('This property cannot be changed.', $violation->getMessage());
        $this->assertSame('code', $violation->getPropertyPath());
    }

    public function testAssociationTypeCodeNotBlank()
    {
        $associationType = $this->createAssociationType();
        $this->getUpdater()->update(
            $associationType,
            []
        );

        $violations = $this->getValidator()->validate($associationType);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'This value should not be blank.',
            $violation->getMessage()
        );
        $this->assertSame('code', $violation->getPropertyPath());
    }

    public function testAssociationTypeCodeRegex()
    {
        $associationType = $this->createAssociationType();
        $this->getUpdater()->update(
            $associationType,
            [
                'code' => 'association-type',
            ]
        );

        $violations = $this->getValidator()->validate($associationType);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'Association type code may contain only letters, numbers and underscores',
            $violation->getMessage()
        );
        $this->assertSame('code', $violation->getPropertyPath());
    }

    public function testAssociationTypeCodeLength()
    {
        $associationType = $this->createAssociationType();
        $this->getUpdater()->update(
            $associationType,
            [
                'code' => str_pad('longCode', 101, "l"),
            ]
        );

        $violations = $this->getValidator()->validate($associationType);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'This value is too long. It should have 100 characters or less.',
            $violation->getMessage()
        );
        $this->assertSame('code', $violation->getPropertyPath());
    }

    public function testAssociationTypeTranslationsLength()
    {
        $associationType = $this->createAssociationType();
        $this->getUpdater()->update(
            $associationType,
            [
                'code'   => 'NEW_SELL',
                'labels' => [
                    'en_US' => str_pad('long_label', 101, "_"),
                ],
            ]
        );

        $violations = $this->getValidator()->validate($associationType);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'This value is too long. It should have 100 characters or less.',
            $violation->getMessage()
        );
        $this->assertSame('translations[0].label', $violation->getPropertyPath());
    }

    public function testAssociationTypeTranslationsLocale()
    {
        $associationType = $this->createAssociationType();
        $this->getUpdater()->update(
            $associationType,
            [
                'code'   => 'NEW_SELL',
                'labels' => [
                    'en_FR' => 'Attribute group',
                ],
            ]
        );

        $violations = $this->getValidator()->validate($associationType);
        $violation = current($violations)[0];

        $this->assertCount(1, $violations);
        $this->assertSame(
            'The locale "en_FR" does not exist.',
            $violation->getMessage()
        );
        $this->assertSame('translations[0].locale', $violation->getPropertyPath());
    }

    /**
     * @return ValidatorInterface
     */
    private function getValidator()
    {
        return $this->get('validator');
    }

    /**
     * @return AssociationTypeInterface
     */
    private function createAssociationType()
    {
        return $this->get('pim_catalog.factory.association_type')->create();
    }

    /**
     * @return ObjectUpdaterInterface
     */
    private function getUpdater()
    {
        return $this->get('pim_catalog.updater.association_type');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
