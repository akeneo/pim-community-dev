<?php

namespace Pim\Bundle\BaseConnectorBundle\Validator\Constraints;

use Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\UiChannelRepository;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\ChoiceValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * Validate that the selected channel exists
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelValidator extends ChoiceValidator
{
    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var UiChannelRepository */
    protected $uiChannelRepository;

    /**
     * @param ChannelRepositoryInterface $channelRepository
     * @param UiChannelRepository        $uiChannelRepository
     */
    public function __construct(ChannelRepositoryInterface $channelRepository, UiChannelRepository $uiChannelRepository)
    {
        $this->channelRepository   = $channelRepository;
        $this->uiChannelRepository = $uiChannelRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $channels = $this->uiChannelRepository->getLabelsIndexedByCode();
        if (0 === count($channels)) {
            throw new ConstraintDefinitionException('No channel is set in the application');
        }
        $constraint->choices = array_keys($channels);

        parent::validate($value, $constraint);
    }
}
