<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Repository\ChannelRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\FamilyRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\LocaleRepository;
use Pim\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface;
use Symfony\Component\Validator\ValidatorInterface;

class CompletenessManagerSpec extends ObjectBehavior
{
    function let(
        FamilyRepository $familyRepository,
        ChannelRepository $channelRepository,
        LocaleRepository $localeRepository,
        CompletenessGeneratorInterface $generator,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith(
            $familyRepository,
            $channelRepository,
            $localeRepository,
            $generator,
            $validator,
            'Pim\Bundle\CatalogBundle\Entity\Channel'
        );
    }
}
