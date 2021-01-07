<?php

use Akeneo\Connectivity\Connection\Application\Audit\Query\CountDailyEventsByConnectionQuery;
use Akeneo\Channel\Component\Query\GetChannelCodeWithLocaleCodesInterface;

class TestUsedClassRenaming
{
    private CountDailyEventsByConnectionQuery $getErrorCountPerConnectionQuery;

    /**
     * TestUsedClassRenaming constructor.
     * @param $getErrorCountPerConnectionQuery
     */
    public function __construct(CountDailyEventsByConnectionQuery $getErrorCountPerConnectionQuery)
    {
        $this->getErrorCountPerConnectionQuery = $getErrorCountPerConnectionQuery;
    }
}

// Fail on renaming base class
//class TestBaseClassRenaming extends CountDailyEventsByConnectionQuery {}


class TestUsedInterfaceRenaming
{
    /** @var GetChannelCodeWithLocaleCodesInterface $getChannelCodeWithLocaleCodesInterface */
    private $getChannelCodeWithLocaleCodesInterface;

    /**
     * TestUsedInterfaceRenaming constructor.
     * @param GetChannelCodeWithLocaleCodesInterface $getChannelCodeWithLocaleCodesInterface
     */
    public function __construct(GetChannelCodeWithLocaleCodesInterface $getChannelCodeWithLocaleCodesInterface)
    {
        $this->getChannelCodeWithLocaleCodesInterface = $getChannelCodeWithLocaleCodesInterface;
    }

}


// Can not rename implemented interface... It fails with Analyze error: "Class Akeneo\Channel\Component\Query\GetChannelCodeWithLocaleCodesInterface not found.
//class TestClassImplementedInterfaceRenaming implements GetChannelCodeWithLocaleCodesInterface {
//    public function findAll(): array
//    {
//        // TODO: Implement findAll() method.
//    }
//}
