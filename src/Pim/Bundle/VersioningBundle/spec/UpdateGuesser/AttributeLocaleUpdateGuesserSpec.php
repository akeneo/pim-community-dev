<?php

namespace spec\Pim\Bundle\VersioningBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;

class AttributeLocaleUpdateGuesserSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\VersioningBundle\UpdateGuesser\AttributeLocaleUpdateGuesser');
    }

    function it_is_an_update_guesser()
    {
        $this->shouldImplement('Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface');
    }

    function it_supports_update_action()
    {
        $this->supportAction(UpdateGuesserInterface::ACTION_UPDATE_COLLECTION)->shouldReturn(true);
        $this->supportAction('foo')->shouldReturn(false);
    }

    function it_guesses_attribute_locale_updates()
    {
        $attribute = new Attribute();
        $em = new MyEntityManager();
        $collection = new PersistentCollection($em, new ClassMetadata('Pim\Bundle\CatalogBundle\Entity\Attribute'), []);
        $collection->setOwner($attribute, ['fieldName' => 'availableLocales', 'inversedBy' => 'foo']);

        $this->guessUpdates($em, $collection, UpdateGuesserInterface::ACTION_UPDATE_COLLECTION)
            ->shouldReturn([$attribute]);
    }
}

class MyEntityManager extends EntityManager {
    /**
     * PersistentCollection can not be a Collaborator, but is final. This current way to test is not ideal, but
     * creating a new EntityManager() requires a lot of mandatory parameters.
     */
    public function __construct()
    {
    }
}
