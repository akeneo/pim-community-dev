<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

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

    /** @var array */
    protected $channels;

    /** @var string */
    protected $values;

    /** @var array */
    protected $attributes = [];

    /** @var array */
    protected $requirements = [];

    /** @var array */
    protected $attributeRequirements;

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
    public function initialize()
    {
        $this->channels = $this->channelRepository->findAll();
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
    public function getActions()
    {

        $this->attributeRequirements = [];

        foreach ($this->getAttributes() as $attribute) {
            $this->setAttributesRequirements($attribute);
        }

        return $this->attributeRequirements;
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
     * Sets values and converts to attribute and requirements arrays
     *
     * @param string $values
     */
    public function setValues($values)
    {
        $this->values = $values;

        $data = (array) json_decode($values, true);

        $this->requirements = array_key_exists(
            'attribute_requirements',
            $data
        ) ? $data['attribute_requirements'] : [];
        $this->attributes = array_key_exists(
            'attributes',
            $data
        ) ? $data['attributes'] : [];

        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getRequirements()
    {
        return $this->requirements;
    }

    /**
     * Creates attributes requirements array
     *
     * @param string $attribute
     */
    protected function setAttributesRequirements($attribute)
    {
        foreach ($this->channels as $channel) {
            $channelCode = $channel->getCode();

            $this->attributeRequirements[] = [
                'attribute_code' => $attribute,
                'channel_code'   => $channelCode,
                'is_required'    => $this->isAttributeRequired($attribute, $channelCode)
            ];
        }
    }

    /**
     * Checks if attribute is required for channel
     *
     * @param string $attribute
     * @param string $channel
     *
     * @return bool
     */
    protected function isAttributeRequired($attribute, $channel)
    {
        if (array_key_exists($channel, $this->getRequirements()) &&
            in_array($attribute, $this->requirements[$channel])
        ) {
            return true;
        }

        return false;
    }
}
