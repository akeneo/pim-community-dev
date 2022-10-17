<?php

namespace Specification\Akeneo\Platform\Bundle\FrameworkBundle\Logging;

use Akeneo\Platform\Bundle\FrameworkBundle\Logging\BoundedContextResolver;
use Akeneo\Platform\Bundle\FrameworkBundle\Logging\ContextLogProcessor;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ContextLogProcessorSpec extends ObjectBehavior
{
    const TEST_SCHEMA = "test_schema";
    const PATH_INFO = "path_info";
    const BOUNDED_CONTEXT = "bounded_context";
    const REF_KEY = 'ref_key';
    const REF_VALUE = 'ref_value';
    const REF_KEY_2 = 'ref_key2';
    const REF_VALUE_2 = 'ref_value2';
    const CONTEXT = 'context';
    const TRACE_ID_VALUE = "request_id_value";
    const CMD_NAME = "cmd_name";

    function let(RequestStack $requestStack, BoundedContextResolver $boundedContextResolver, Request $request)
    {
        $request->getSchemeAndHttpHost()->willReturn(self::TEST_SCHEMA);
        $request->getPathInfo()->willReturn(self::PATH_INFO);
        $boundedContextResolver->fromRequest($request)->willReturn(self::BOUNDED_CONTEXT);
        $this->beConstructedWith($requestStack, $boundedContextResolver);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ContextLogProcessor::class);
    }

    private function initIncomingRecord(): array
    {
        return [self::REF_KEY => self::REF_VALUE];
    }

    private function initIncomingContextfullRecord(): array
    {
        return [
            self::REF_KEY => self::REF_VALUE,
            self::CONTEXT => [
                self::REF_KEY_2 => self::REF_VALUE_2
            ]
        ];
    }

    function it_will_enrich_request_with_path_info_and_requestid(
        RequestStack $requestStack,
        Request $request,
        BoundedContextResolver $boundedContextResolver,
        HeaderBag $headerBag
    ) {
        $requestStack->getMainRequest()->willReturn($request);
        $request->headers = $headerBag;
        $headerBag->get('X-Datadog-Trace-Id')->willReturn(self::TRACE_ID_VALUE);

        $returnedMock = $this->__invoke($this->initIncomingContextfullRecord());

        //checks
        $returnedMock->shouldHaveKeyWithValue(self::REF_KEY, self::REF_VALUE); //unchanged existing record

        $returnedMock['context']->shouldHaveKeyWithValue(
            self::REF_KEY_2,
            self::REF_VALUE_2
        ); //unchanged existing context
        $returnedMock['context']->shouldHaveKeyWithValue('path_info', self::TEST_SCHEMA . self::PATH_INFO);
        $returnedMock['context']->shouldHaveKeyWithValue('akeneo_context', self::BOUNDED_CONTEXT);
        $returnedMock->shouldHaveKeyWithValue('trace_id',self::TRACE_ID_VALUE);

    }

    function it_will_enrich_request_with_path_info_and_generated_requestid(
        RequestStack $requestStack,
        Request $request,
        BoundedContextResolver $boundedContextResolver,
        HeaderBag $headerBag
    ) {
        $requestStack->getMainRequest()->willReturn($request);
        $request->headers = $headerBag;

        $returnedMock = $this->__invoke($this->initIncomingContextfullRecord());

        //checks
        $returnedMock->shouldHaveKeyWithValue(
            self::REF_KEY,
            self::REF_VALUE
        ); //unchanged existing record

        $returnedMock['context']->shouldHaveKeyWithValue(
            self::REF_KEY_2,
            self::REF_VALUE_2
        ); //unchanged existing context
        $returnedMock['context']->shouldHaveKeyWithValue(
            'path_info',
            self::TEST_SCHEMA . self::PATH_INFO
        );
        $returnedMock['context']->shouldHaveKeyWithValue(
            'akeneo_context',
            self::BOUNDED_CONTEXT
        );
        $returnedMock['trace_id']->shouldMatch(
            '/\w{8}-\w{4}-\w{4}-\w{4}-\w{12}/'
        ); //UUID v4 pattern matching https://stackoverflow.com/a/6223251

    }

    function it_can_initialize_command_with_known_context(
        Command $cmd,
        BoundedContextResolver $boundedContextResolver
    ) {
        $cmd->getName()->willReturn(self::CMD_NAME);
        $boundedContextResolver->fromCommand($cmd)->willReturn(self::BOUNDED_CONTEXT);
        $this->initCommandContext($cmd);
        $returnedMock = $this->__invoke($this->initIncomingContextfullRecord());
        $returnedMock->shouldHaveKeyWithValue(
            self::REF_KEY,
            self::REF_VALUE
        ); //unchanged existing record
        $returnedMock['context']->shouldHaveKeyWithValue(
            self::REF_KEY_2,
            self::REF_VALUE_2
        ); //unchanged existing context
        $returnedMock['context']->shouldHaveKeyWithValue(
            'cmd_name',
            self::CMD_NAME
        );
        $returnedMock['context']->shouldHaveKeyWithValue(
            'akeneo_context',
            self::BOUNDED_CONTEXT
        );
        $returnedMock['trace_id']->shouldMatch(
            '/\w{8}-\w{4}-\w{4}-\w{4}-\w{12}/'
        ); //UUID v4 pattern matching https://stackoverflow.com/a/6223251
    }

    function it_can_initialize_command_with_unknown_context(
        Command $cmd,
        BoundedContextResolver $boundedContextResolver
    ) {
        $cmd->getName()->willReturn(self::CMD_NAME);
        $boundedContextResolver->fromCommand($cmd)->willReturn(null);
        $this->initCommandContext($cmd);
        $returnedMock = $this->__invoke($this->initIncomingContextfullRecord());
        $returnedMock['context']->shouldHaveKeyWithValue('akeneo_context', "Unknown context");
    }

}
