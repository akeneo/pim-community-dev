<?php

declare(strict_types=1);

require __DIR__.'/../../../../vendor/autoload.php';

use JsonSchema\Validator;

/**
 * Check if every Request Contracts in the $requestContractsDir folder
 * respect their JSON Schemas.
 */
function checkJsonSchemas(string $requestContractsDir): bool
{
    $hasError = false;
    $jsonSchemasDir = sprintf('%s/%s', __DIR__, 'shared/schemas/');
    $requestContracts = getRequestContracts($requestContractsDir);

    foreach ($requestContracts as $requestContract) {
        $requestContractRelativePath = str_replace($requestContractsDir, '', $requestContract);
        $associatedJsonSchemaPath = sprintf(
            '%s/%s/schema.json',
            $jsonSchemasDir,
            dirname($requestContractRelativePath)
        );

        $jsonSchemaExists = file_exists($associatedJsonSchemaPath);

        // Check if the Request Contract has a JSON Schema
        if (!$jsonSchemaExists) {
            // TODO: re enable this part once we'll have all needed JSON Schema
//            $hasError = true;
//            $message = sprintf('Missing JSON Schema for following request contract: "%s"', $requestContract);
//            writeln($message, 'error');

            continue;
        }

        $schemaFileContents = file_get_contents($associatedJsonSchemaPath);
        $jsonSchema = json_decode($schemaFileContents, true);

        $fileContents = file_get_contents($requestContract);
        $responseContent = json_decode($fileContents, true);

        // Check if the Request Contract respects its JSON Schema
        $response = $responseContent['response'];
        $validator = new Validator();
        $normalizedResponse = Validator::arrayToObjectRecursive($response);
        $validator->validate($normalizedResponse, $jsonSchema);

        $errors = $validator->getErrors();
        if (!empty($validator->getErrors())) {
            $hasError = true;
            displayJsonSchemaErrors($errors, $requestContract);
        }
    }

    return $hasError;
}

function displayJsonSchemaErrors(array $errors, string $requestContract)
{
    foreach ($errors as $error) {
        $message = sprintf(
            'Request Contract "%s" does not respect its JSON Schema. Property "%s": "%s"',
            str_replace(__DIR__, '', $requestContract),
            $error['property'],
            $error['message']
        );

        writeln($message, 'error');
    }
}

function writeln($text, $type = 'normal'): void
{
    $RESET = "\e[0m";
    $BOLD_WHITE = "\033[1;38m";
    $GREEN = "\033[0;32m";
    $YELLOW = "\033[0;33m";
    $BG_RED = "\033[41m";

    switch ($type) {
        case 'info':
            echo $GREEN . $text;
            break;
        case 'error':
            echo $BOLD_WHITE . $BG_RED . $text;
            break;
        case 'comment':
            echo $YELLOW . $text;
            break;
        default:
            echo $RESET . $text;
            break;
    }

    echo PHP_EOL;
}

function removeEmptyLine(array $result): array
{
    if (!empty($result)) {
        unset($result[\count($result) - 1]);
    }

    return $result;
}

function removeDoubleSlashes(array $paths): array
{
    return array_map(
        fn (string $path) => str_replace('//', '/', $path),
        $paths
    );
}

/**
 * Find and return the list of request contract
 *
 * @return string[]
 */
function getRequestContracts(string $requestContractsDir): array
{
    $command = sprintf('find %s -type f -name "*.json" -not -path "/*Connector*"', $requestContractsDir);
    $shellResult = shell_exec($command);
    $result = explode("\n", $shellResult);

    return removeDoubleSlashes(removeEmptyLine($result));
}

/**
 * Main
 */
$backendRequestContractsDir = sprintf('%s/%s', __DIR__, 'back/Integration/Resources/responses/');
$frontendRequestContractsDir = sprintf('%s/%s', __DIR__, 'front/integration/responses/');

$hasError = checkJsonSchemas($backendRequestContractsDir) ||checkJsonSchemas($frontendRequestContractsDir);

if ($hasError === false) {
    writeln('No error found in Request Contracts', 'info');
}

$exitCode = $hasError === true ? 1 : 0;
exit($exitCode);
