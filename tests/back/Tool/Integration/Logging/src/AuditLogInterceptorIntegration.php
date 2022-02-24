<?php

namespace AkeneoTest\Tool\Integration\Logging\src;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\LoggingBundle\Domain\Service\AuditLogInterceptor;
use PHPUnit\Framework\Assert;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuditLogInterceptorIntegration extends TestCase
{
    private AuditLogInterceptor $auditLogInterceptor;
    private InMemoryLogger $logger;
    private TestServiceFixture $testServiceFixture;

    protected function getConfiguration()
    {

    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->auditLogInterceptor = $this->get(AuditLogInterceptor::class);
        $this->testServiceFixture = $this->get(TestServiceFixture::class);

        $this->logger = new InMemoryLogger();
        $reflectionClass = new \ReflectionClass($this->auditLogInterceptor);
        $reflectionProperty = $reflectionClass->getProperty('logger');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->auditLogInterceptor, $this->logger);
    }

    public function test_public_method_call_is_audited()
    {
        Assert::assertEquals(TestServiceFixture::SOMETHING_PUBLIC_DONE, $this->testServiceFixture->doSomethingPublic());

        Assert::assertEquals([
                [
                    'log_level' => LogLevel::INFO,
                    'message' => '>> AkeneoTest\Tool\Integration\Logging\src\TestServiceFixture->doSomethingPublic: LOC 18',
                    'context' => [
                        'class_name'=>'AkeneoTest\Tool\Integration\Logging\src\TestServiceFixture',
                        'method_name'=>'doSomethingPublic',
                        'line_number' => 18,
                        'execution_status' => 'INCOMING'
                    ]],
                [
                    'log_level' => LogLevel::INFO,
                    'message' => '<< AkeneoTest\Tool\Integration\Logging\src\TestServiceFixture->doSomethingPublic: LOC 18',
                    'context' => [
                        'class_name'=>'AkeneoTest\Tool\Integration\Logging\src\TestServiceFixture',
                        'method_name'=>'doSomethingPublic',
                        'line_number' => 18,
                        'execution_status' => 'OUTGOING'
                    ]]
            ]
            , $this->filteredLogMessages());
    }


    public function test_protected_method_call_is_audited()
    {
        Assert::assertEquals(TestServiceFixture::SOMETHING_PROTECTED_DONE, $this->testServiceFixture->callSomeProtectedMethod());
        Assert::assertEquals('doSomethingProtected', $this->logger->getLoggedMessages()[0]['context']['method_name']);
    }

    public function test_errored_method_call_is_audited() {
        $e = null;
        try {
            $this->testServiceFixture->callErroredMethod();
        } catch (\Throwable $t) {
            $e = $t;
        }
        Assert::assertEquals("Error thrown during callErroredMethod",$e->getMessage());
        Assert::assertEquals(\Error::class,get_class($e));
        $messages =$this->filteredLogMessages();
        Assert::assertEquals("ERROR<< AkeneoTest\Tool\Integration\Logging\src\TestServiceFixture->callErroredMethod: LOC 28",$messages[1]['message']);
        Assert::assertEquals('callErroredMethod', $messages[1]['context']['method_name']);
    }

    private function filteredLogMessages() {
        $messages = $this->logger->getLoggedMessages();
        foreach ($messages as $index => $message) {
            $message['context'] = $this->filterOutSideEffectContext($message['context'], ['latency_microsecs']);
            $messages[$index]=$message;
        }
        return $messages;
    }
    private function filterOutSideEffectContext(array $inputArray, array $filteredContextKeys): array
    {
        foreach ($filteredContextKeys as $key) {
            unset($inputArray[$key]);
        }
        return $inputArray;
    }


}
