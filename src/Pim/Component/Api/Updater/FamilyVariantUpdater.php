<?php

declare(strict_types=1);

namespace Pim\Component\Api\Updater;

use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\ApiBundle\Documentation;
use Pim\Component\Api\Exception\DocumentedHttpException;

/**
 * Update the family variant properties
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantUpdater implements ObjectUpdaterInterface
{
    /** @var ObjectUpdaterInterface */
    private $familyUpdater;

    /**
     * @param ObjectUpdaterInterface $familyUpdater
     */
    public function __construct(ObjectUpdaterInterface $familyUpdater)
    {
        $this->familyUpdater = $familyUpdater;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DocumentedHttpException
     */
    public function update($familyVariant, array $data, array $options = []): ObjectUpdaterInterface
    {
        if (isset($data['family'])) {
            $exception = UnknownPropertyException::unknownProperty('family');
            throw new DocumentedHttpException(
                Documentation::URL . 'post_families__family_code__variants',
                sprintf('%s Check the standard format documentation.', $exception->getMessage()),
                $exception
            );
        }
        $data['family'] = isset($options['familyCode']) ? $options['familyCode'] : null;

        $this->familyUpdater->update($familyVariant, $data, $options);

        return $this;
    }
}
