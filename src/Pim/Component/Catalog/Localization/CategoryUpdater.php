<?php

namespace Pim\Component\Catalog\Localization;

use Akeneo\Component\Localization\Model\TranslatableInterface;
use Akeneo\Component\Localization\TranslatableUpdater;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;

/**
 * Decorates category updater to translate labels.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryUpdater implements ObjectUpdaterInterface
{
    /** @var ObjectUpdaterInterface */
    protected $categoryUpdater;

    /** @var TranslatableUpdater */
    protected $translatableUpdater;

    /**
     * @param ObjectUpdaterInterface $categoryUpdater
     * @param TranslatableUpdater    $translatableUpdater
     */
    public function __construct(ObjectUpdaterInterface $categoryUpdater, TranslatableUpdater $translatableUpdater)
    {
        $this->categoryUpdater = $categoryUpdater;
        $this->translatableUpdater = $translatableUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function update($category, array $data, array $options = [])
    {
        try {
            $this->categoryUpdater->update($category, $data, $options);

            if (isset($data['labels']) && $category instanceof TranslatableInterface) {
                $this->translatableUpdater->update($category, $data['labels']);
            }
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }

        return $this;
    }
}
