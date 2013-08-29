<?php

namespace Oro\Bundle\UserBundle\Provider;

use Symfony\Component\Translation\TranslatorInterface;
use Oro\Bundle\UserBundle\Model\Gender;

class GenderProvider
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var array
     */
    protected $choices = array(
        Gender::MALE   => 'oro.user.gender.male',
        Gender::FEMALE => 'oro.user.gender.female',
    );

    /**
     * @var array
     */
    protected $translatedChoices;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public function getChoices()
    {
        if (null === $this->translatedChoices) {
            $this->translatedChoices = array();
            foreach ($this->choices as $name => $label) {
                $this->translatedChoices[$name] = $this->translator->trans($label);
            }
        }

        return $this->translatedChoices;
    }

    /**
     * @param string $name
     * @return string
     * @throws \LogicException
     */
    public function getLabelByName($name)
    {
        $choices = $this->getChoices();
        if (!isset($choices[$name])) {
            throw new \LogicException(sprintf('Unknown gender with name "%s"', $name));
        }

        return $choices[$name];
    }
}
