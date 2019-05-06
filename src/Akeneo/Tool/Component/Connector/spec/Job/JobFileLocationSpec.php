<?php

namespace spec\Akeneo\Tool\Component\Connector\Job;

use PhpSpec\ObjectBehavior;

class JobFileLocationSpec extends ObjectBehavior
{
    function it_is_built_for_local()
    {
        $this->beConstructedWith('/my/path/to/local/file.csv', false);
        $this->isRemote()->shouldReturn(false);
        $this->path()->shouldReturn('/my/path/to/local/file.csv');
        $this->url()->shouldReturn('/my/path/to/local/file.csv');
    }

    function it_is_built_for_remote()
    {
        $this->beConstructedWith('/my/path/to/remote/file.csv', true);
        $this->isRemote()->shouldReturn(true);
        $this->path()->shouldReturn('/my/path/to/remote/file.csv');
        $this->url()->shouldReturn('pim_remote:///my/path/to/remote/file.csv');
    }

    function it_is_built_from_local_location_url()
    {
        $this->beConstructedThrough('parseUrl', ['/my/path/to/local/file.csv']);
        $this->isRemote()->shouldReturn(false);
        $this->path()->shouldReturn('/my/path/to/local/file.csv');
    }

    function it_is_built_from_remote_location_url()
    {
        $this->beConstructedThrough('parseUrl', ['pim_remote:///my/path/to/remote/file.csv']);
        $this->isRemote()->shouldReturn(true);
        $this->path()->shouldReturn('/my/path/to/remote/file.csv');
    }
}
