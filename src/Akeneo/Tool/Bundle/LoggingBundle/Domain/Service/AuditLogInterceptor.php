<?php


namespace Akeneo\Tool\Bundle\LoggingBundle\Domain\Service;

use Akeneo\Tool\Bundle\LoggingBundle\Domain\Model\AuditLog;
use CG\Proxy\MethodInterceptorInterface;
use CG\Proxy\MethodInvocation;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuditLogInterceptor implements MethodInterceptorInterface
{
    use PHPAttributeAware;//use for php attribute customization.

    const EXECUTION_STATUS = 'execution_status';
    const CLASS_NAME = "class_name";
    const METHOD_NAME = "method_name";
    const LINE_NUMBER = "line_number";

    private ?array $attributes=null;

    public function __construct(private LoggerInterface $logger)
    {
    }

    public function intercept(MethodInvocation $invocation)
    {
        $this->initAttributes($invocation, AuditLog::class);

        $baseContext = $this->initBaseContext($invocation);
        $baseMessage = $this->initBaseMessage($baseContext);

        $this->logger->info(">> {$baseMessage}", $this->enrichWithExecution($baseContext, $this->startExecutionContext()));
        $startTime = hrtime(true);//side effect this could be a

        try {
            $returnedValue = $invocation->proceed();
            $this->logger->info("<< {$baseMessage}", $this->enrichWithExecution($baseContext, $this->endExecutionContext($startTime)));
            return $returnedValue;
        } catch (\Throwable $e) {
            $this->logger->error("ERROR<< {$baseMessage}", $this->enrichWithExecution($baseContext, $this->endExecutionContext($startTime, $e)));
            throw $e;
        }
    }
    private function enrichWithExecution(array $baseContext, array $additionalContext): array
    {
        return array_merge($baseContext, $additionalContext);
    }

    private function startExecutionContext(): array
    {
        return [self::EXECUTION_STATUS => 'INCOMING'];
    }

    private function endExecutionContext(int $startTime, ?\Throwable $throwable = null): array
    {
        $latencyInMicroSecs = $this->computeLatencyInMicroSecs($startTime);

        $context = ['latency_microsecs' =>$latencyInMicroSecs, self::EXECUTION_STATUS => $throwable ? 'ERROR' : 'OUTGOING'];
        if ($throwable) {//TODO enhance with dump_exception toggle in the php attribute (default = false: exception should be logged at system/transaction boundaries).
            $context['exception'] = $throwable;
        }
        return $context;
    }

    protected function computeLatencyInMicroSecs($startTime)
    {
        return intdiv(hrtime(true) - $startTime, 1000);
    }

    protected function initBaseContext(MethodInvocation $invocation): array
    {
        $className = $invocation->reflection
            ->getDeclaringClass()
            ->getName();
        $methodName = $invocation->reflection
            ->getName();
        $startLine = $invocation->reflection->getStartLine();

        $baseContext = [self::CLASS_NAME => $className, self::METHOD_NAME => $methodName, self::LINE_NUMBER => $startLine];
        return $baseContext;
    }

    protected function initBaseMessage(array $baseContext): string
    {
        return "{$baseContext[self::CLASS_NAME]}->{$baseContext[self::METHOD_NAME]}: LOC {$baseContext[self::LINE_NUMBER]}";
    }
}
