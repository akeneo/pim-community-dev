<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\SetAttributeRequirementsType;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;

/**
 * Set attribute requirements
 *
 * Applied on family grid
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetAttributeRequirements extends AbstractMassEditOperation
{
    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /**
     * @param ChannelRepositoryInterface   $channelRepository
     * @param string                       $jobInstanceCode
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        $jobInstanceCode
    ) {
        parent::__construct($jobInstanceCode);

        $this->channelRepository = $channelRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return SetAttributeRequirementsType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormOptions()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getOperationAlias()
    {
        return 'set-attribute-requirements';
    }

    /**
     * Sets values and converts to attribute, requirements and actions array
     *
     * @param string $values
     *
     * @return $this
     */
    public function setValues($values)
    {
        $this->actions = $this->getActionsFromValues($values);

        return $this;
    }

    /**
     * Always return empty values. Used by form type
     *
     * @return array
     */
    public function getValues()
    {
        return [];
    }


    /**
     * @return array
     */
    protected function getActionsFromValues($values)
    {
        $attributeRequirements = [];

        $requirements = $this->getRequirementsFromValues($values);
        $attributes = $this->getAttributesFromValues($values);

        $channelCodes = array_map(function (ChannelInterface $channel) {
            return $channel->getCode();
        }, $this->channelRepository->findAll());

        foreach ($attributes as $attributeCode) {
            foreach ($channelCodes as $channelCode) {
                $attributeRequirements[] = [
                    'attribute_code' => $attributeCode,
                    'channel_code'   => $channelCode,
                    'is_required'    => array_key_exists($channelCode, $requirements) &&
                        in_array($attributeCode, $requirements[$channelCode])
                ];
            }
        }

        return $attributeRequirements;
    }

    /**
     * @param string $values
     *
     * @return string[]
     */
    protected function getAttributesFromValues($values)
    {
        $data = (array) json_decode($values, true);

        if (array_key_exists('attributes', $data)) {
            return $data['attributes'];
        }

        return [];
    }

    /**
     * @param string $values
     *
     * @return array
     */
    protected function getRequirementsFromValues($values)
    {
        $data = (array) json_decode($values, true);

        if (array_key_exists('attribute_requirements', $data)) {
            return $data['attribute_requirements'];
        }

        return [];
    }
}
