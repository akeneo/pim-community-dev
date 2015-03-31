<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\Denormalization;

use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\BaseConnectorBundle\Processor\Denormalization\ArrayConverter\StandardArrayConverterInterface;
use Pim\Bundle\CatalogBundle\Exception\UpdaterException;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Updater\UpdaterInterface;

/**
 * AttributeOption option import processor, allows to,
 *  - create / update attributeOption options
 *  - return the valid attributeOption options, throw exceptions to skip invalid ones
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionProcessor extends AbstractProcessor
{
    /** @var StandardArrayConverterInterface */
    protected $arrayConverter;

    /** @var UpdaterInterface */
    protected $optionUpdater;

    /** @var string */
    protected $class;

    /**
     * @param StandardArrayConverterInterface       $arrayConverter   format converter
     * @param IdentifiableObjectRepositoryInterface $optionRepository option repository
     * @param UpdaterInterface                      $optionUpdater    option updater
     * @param string                                $class            attribute option class
     */
    public function __construct(
        StandardArrayConverterInterface $arrayConverter,
        IdentifiableObjectRepositoryInterface $optionRepository,
        UpdaterInterface $optionUpdater,
        $class
    ) {
        parent::__construct($optionRepository);
        $this->arrayConverter = $arrayConverter;
        $this->optionUpdater  = $optionUpdater;
        $this->class          = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $convertedItem = $this->convertItemData($item);
        $attributeOption = $this->findOrCreateAttributeOption($convertedItem);
        $this->updateAttributeOption($attributeOption, $convertedItem, $item);

        return $attributeOption;
    }

    /**
     * @param array $item
     *
     * @return array
     */
    protected function convertItemData(array $item)
    {
        return $this->arrayConverter->convert($item);
    }

    /**
     * @param array $convertedItem
     *
     * @return AttributeOptionInterface
     */
    protected function findOrCreateAttributeOption(array $convertedItem)
    {
        $attributeOption = $this->findObject($this->repository, $convertedItem);
        if ($attributeOption === null) {
            return new $this->class();
        }

        return $attributeOption;
    }

    /**
     * @param AttributeOptionInterface $attributeOption
     * @param array                    $convertedItem
     * @param array                    $originalItem
     *
     * @return AttributeOptionInterface
     *
     * @throws UpdaterException
     */
    protected function updateAttributeOption(
        AttributeOptionInterface $attributeOption,
        array $convertedItem,
        array $originalItem
    ) {
        // TODO: ugly fix to workaround issue with "attribute.group.code: This value should not be blank."
        // in case of existing option, attribute is a proxy, attribute group too, the validated group code is null
        ($attributeOption->getAttribute() !== null) ? $attributeOption->getAttribute()->getGroup()->getCode() : null;

        try {
            $this->optionUpdater->update($attributeOption, $convertedItem);
        } catch (UpdaterException $exception) {
            $this->skipItemWithConstraintViolations($originalItem, $exception->getViolations(), $exception);
        }

        return $attributeOption;
    }
}
