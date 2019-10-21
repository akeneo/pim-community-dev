<?php

namespace Akeneo\Tool\Bundle\ApiBundle\Stream;

use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueValuesSet;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Read the php input line by line, and forward the content of each line to a controller.
 * Only a single line is loaded in memory at a time.
 *
 * Each line represents the content of a subrequest that will be forwarded to a controller.
 * Each response's content of a subrequest is then flushed in the global response, as a stream.
 *
 * Therefore, response's headers of the different subrequests are not returned, only the content.
 *
 * Do note that headers of the global response can not be changed as soon as you are streaming the response's content,
 * even if you have an error. This is due to the HTTP protocol.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StreamResourceResponse
{
    const CONTENT_TYPE = 'application/vnd.akeneo.collection+json';

    /** @var HttpKernelInterface */
    protected $httpKernel;

    /** @var UniqueValuesSet */
    protected $uniqueValuesSet;

    /** @var string */
    protected $controllerName;

    /** @var string */
    protected $identifierKey;

    /** @var array */
    protected $configuration;

    /**
     * @param HttpKernelInterface $httpKernel
     * @param UniqueValuesSet     $uniqueValuesSet
     * @param array               $configuration
     * @param string              $controllerName
     * @param string              $identifierKey
     */
    public function __construct(
        HttpKernelInterface $httpKernel,
        UniqueValuesSet $uniqueValuesSet,
        array $configuration,
        $controllerName,
        $identifierKey
    ) {
        $this->httpKernel = $httpKernel;
        $this->uniqueValuesSet = $uniqueValuesSet;
        $this->configuration = $configuration;
        $this->controllerName = $controllerName;
        $this->identifierKey = $identifierKey;
    }

    /**
     * @param resource $resource      resource containing the whole data to process
     * @param array    $uriParameters default uri parameters to use when forwarding requests
     * @param null|callable $postResponseCallable inject callable to execute after output response flushing
     *
     * @throws HttpException
     *
     * @return StreamedResponse
     */
    public function streamResponse($resource, array $uriParameters = [], callable $postResponseCallable = null)
    {
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', static::CONTENT_TYPE);

        $this->checkLineNumberInInput($resource);

        $response->setCallback(function () use ($resource, $uriParameters, $postResponseCallable) {
            rewind($resource);
            $this->ensureOutputBufferingIsStarted();

            $lineNumber = 1;
            $bufferSize = $this->configuration['input']['buffer_size'];
            $line = stream_get_line($resource, $bufferSize + 1, PHP_EOL);

            while (false !== $line) {
                try {
                    $this->checkLineLength($line, $resource);

                    $data = json_decode($line, true);
                    if (null === $data) {
                        throw new BadRequestHttpException('Invalid json message received');
                    }
                    if (!isset($data[$this->identifierKey]) || '' === trim($data[$this->identifierKey])) {
                        throw new UnprocessableEntityHttpException(sprintf('%s is missing.', ucfirst($this->identifierKey)));
                    }

                    $response = [
                        'line'               => $lineNumber,
                        $this->identifierKey => $data[$this->identifierKey],
                    ];

                    $uriParameters['code']  = $data[$this->identifierKey];
                    $subResponse = $this->forward($uriParameters, $line);

                    if ('' !== $subResponse->getContent()) {
                        $subResponse = json_decode($subResponse->getContent(), true);
                        if (isset($subResponse['code'])) {
                            $response['status_code'] = $subResponse['code'];
                            unset($subResponse['code']);
                        }

                        $response = array_merge($response, $subResponse);
                    } else {
                        $response['status_code'] = $subResponse->getStatusCode();
                    }
                } catch (HttpException $e) {
                    $response = [
                        'line'        => $lineNumber,
                        'status_code' => $e->getStatusCode(),
                        'message'     => $e->getMessage(),
                    ];
                } catch (\Throwable $e) {
                    // Ensure the post actions are executed even if an error occurred
                    if (is_callable($postResponseCallable)) {
                        $postResponseCallable();
                    }

                    throw $e;
                }

                $this->uniqueValuesSet->reset();
                $this->flushOutputBuffer($response, $lineNumber);
                $lineNumber++;
                $line = stream_get_line($resource, $bufferSize + 1, PHP_EOL);
            }

            if (is_callable($postResponseCallable)) {
                $postResponseCallable();
            }
        });

        return $response;
    }

    /**
     * Checks that the number of resources to process is inferior to the maximum allowed.
     *
     * @param resource $resource
     *
     * @throws HttpException
     */
    protected function checkLineNumberInInput($resource)
    {
        $maxNumberResources = $this->configuration['input']['max_resources_number'];

        $lineNumber = 0;

        while (false !== $this->getNextLine($resource)) {
            $lineNumber++;
            if ($lineNumber > $maxNumberResources) {
                throw new HttpException(
                    Response::HTTP_REQUEST_ENTITY_TOO_LARGE,
                    sprintf('Too many resources to process, %s is the maximum allowed.', $maxNumberResources)
                );
            }
        }
    }

    /**
     * Forwards the request to another controller.
     *
     * Forwarding the request allows to get the same response as a single request,
     * passing through the kernel and therefore the listeners.
     *
     * It would not be possible to do that by calling directly the controller.
     *
     * @param array  $uriParameters uri parameters of the controller
     * @param string $content       content of the subrequest
     *
     * @return Response A Response instance
     */
    public function forward($uriParameters, $content)
    {
        $parameters = array_merge(['_controller' => $this->controllerName], $uriParameters);
        $subRequest = new Request([], [], $parameters, [], [], [], $content);
        $subRequest->setRequestFormat('json');
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        return $response;
    }

    /**
     * Returns the current line.
     * If the line is too long for the buffer, consumes the rest of the line.
     *
     * @param resource $resource
     *
     * @return string content of the line, truncated by the buffer size if the line is too long
     */
    protected function getNextLine($resource)
    {
        $bufferSize = $this->configuration['input']['buffer_size'];

        $line = stream_get_line($resource, $bufferSize + 1, PHP_EOL);
        $buffer = $line;

        while (strlen($buffer) > $bufferSize) {
            $buffer = stream_get_line($resource, $bufferSize + 1, PHP_EOL);
        }

        return $line;
    }

    /**
     * Checks the length of the line.
     *
     * If the line is too long for the buffer, consumes the rest of the line
     * and throws an error 413.
     *
     * @param string   $line
     * @param resource $resource
     *
     * @throws HttpException
     */
    protected function checkLineLength($line, $resource)
    {
        $bufferSize = $this->configuration['input']['buffer_size'];

        $bufferSizeExceeded = strlen($line) > $bufferSize;
        $buffer = $line;

        while (strlen($buffer) > $bufferSize) {
            $buffer = stream_get_line($resource, $bufferSize + 1, PHP_EOL);
        }

        if ($bufferSizeExceeded) {
            throw new HttpException(Response::HTTP_REQUEST_ENTITY_TOO_LARGE, 'Line is too long.');
        }
    }

    /**
     * Flushes the buffer with the content encoded with JSON.
     * A carriage return is added to separate the response's content
     * from the next subrequest's response.
     *
     * @param array $content
     * @param int   $lineNumber
     */
    protected function flushOutputBuffer($content, $lineNumber)
    {
        $jsonContent = 1 === $lineNumber ? json_encode($content) : PHP_EOL . json_encode($content);

        echo $jsonContent;
        ob_flush();
        flush();
    }

    /**
     * The directive "ouput_buffering" could be disabled in the php configuration of some providers.
     * In this case, we have to start the output buffering manually.
     * Do note that is not possible to close all the output buffers before flushing the data,
     * because it's needed for the tests.
     */
    protected function ensureOutputBufferingIsStarted()
    {
        if (0 === ob_get_level()) {
            ob_start();
        }
    }
}
