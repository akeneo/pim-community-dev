<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\Denormalization;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\BaseConnectorBundle\Processor\Denormalization\ArrayConverter\StandardArrayConverterInterface;
use Pim\Bundle\CatalogBundle\Exception\BusinessValidationException;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Updater\UpdaterInterface;

/**
 * Attribute option import processor, allows to,
 *  - create / update
 *  - skip invalid ones
 *  - return the valid ones
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
        try {
            $this->optionUpdater->update($attributeOption, $convertedItem);
        } catch (BusinessValidationException $exception) {
            $this->skipItemWithConstraintViolations($item, $exception->getViolations());
        }

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
}
