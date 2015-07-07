<?php

namespace spec\Pim\Bundle\VersioningBundle\Manager;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\SmartManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Builder\VersionBuilder;
use Pim\Bundle\VersioningBundle\Manager\VersionContext;
use Pim\Bundle\VersioningBundle\Model\Version;
use Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use Prophecy\Argument;

class VersionContextSpec extends ObjectBehavior
{
    function it_adds_and_returns_a_default_context()
    {
        $this->addContextInfo('my super context');
        $this->getContextInfo()->shouldReturn('my super context');
    }
    function it_adds_and_returns_a_context_with_fqcn()
    {
        $this->addContextInfo('my super context with fqcn', 'MyClass');
        $this->getContextInfo('MyClass')->shouldReturn('my super context with fqcn');
    }
}
