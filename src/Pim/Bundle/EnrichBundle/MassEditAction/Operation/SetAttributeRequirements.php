<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

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

    /** @var string */
    protected $values;

    /** @var string[] */
    protected $attributes;

    /** @var array */
    protected $requirements;

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
        return 'pim_enrich_mass_set_attribute_requirements';
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
     * Gets values
     *
     * @return string
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Gets attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Gets requirements
     *
     * @return array
     */
    public function getRequirements()
    {
        return $this->requirements;
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
        $this->values = $values;
        $this->attributes = $this->getAttributesFromValues();
        $this->requirements = $this->getRequirementsFromValues();
        $this->actions = $this->getActionsFromValues();

        return $this;
    }

    /**
     * @return string[]
     */
    protected function getAttributesFromValues()
    {
        $data = (array) json_decode($this->values, true);

        if (array_key_exists('attributes', $data)) {
            return $data['attributes'];
        }

        return [];
    }

    /**
     * @return array
     */
    protected function getRequirementsFromValues()
    {
        $data = (array) json_decode($this->values, true);

        if (array_key_exists('attribute_requirements', $data)) {
            return $data['attribute_requirements'];
        }

        return [];
    }

    /**
     * @return array
     */
    protected function getActionsFromValues()
    {
        $attributeRequirements = [];
        $channelCodes = array_map(function (ChannelInterface $channel) {
            return $channel->getCode();
        }, $this->channelRepository->findAll());

        foreach ($this->getAttributes() as $attributeCode) {
            foreach ($channelCodes as $channelCode) {
                $attributeRequirements[] = [
                    'attribute_code' => $attributeCode,
                    'channel_code'   => $channelCode,
                    'is_required'    => array_key_exists($channelCode, $this->getRequirements()) &&
                        in_array($attributeCode, $this->getRequirements()[$channelCode])
                ];
            }
        }

        return $attributeRequirements;
    }
}
