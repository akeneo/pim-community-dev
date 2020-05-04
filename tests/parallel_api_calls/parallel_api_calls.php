<?php

declare(strict_types=1);

function usage(string $scriptName)
{
    fprintf(STDERR, "Usage: %s -P <concurrent_calls> -c <client_id> -s <secret> -u <username> -p <password>\n", $scriptName);
    exit(1);
}

$options = getopt("P:c:s:u:p:");

if (false === $options) {
    usage($argv[0]);
}

$parallelism = (int) $options['P'];

$clientId = $options['c'];
$secret = $options['s'];
$username = $options['u'];
$password = $options['p'];

if (0 >= $parallelism ||null === $clientId || null === $secret || null === $username || null === $password) {
    usage($argv[0]);
}

$basicAuth = base64_encode($clientId.':'.$secret);

$exitCode = 0;

echo "Authenticating...\n";
$authToken = getAuthToken($basicAuth, $username, $password);

echo "Checking product API...\n";
echo "\tGetting product data...\n";
$productData = getData($authToken, '/api/rest/v1/products?limit=100') ;

try {
    echo "\tSending back product data...\n";
    sendData($authToken, '/api/rest/v1/products', $productData, $parallelism);
} catch (RuntimeException $e) {
    echo "** Product API failed! **\n";
    echo $e->getMessage()."\n";
    $exitCode = 1;
}

echo "Checking product model API...\n";
echo "\tGetting product model data...\n";
$productModelData = getData($authToken, '/api/rest/v1/product-models?limit=100') ;

try {
    echo "\tSending back product model data...\n";
    sendData($authToken, '/api/rest/v1/product-models', $productModelData, $parallelism);
} catch (RuntimeException $e) {
    echo "** Product Model API failed! **\n";
    echo $e->getMessage()."\n";
    $exitCode = 1;
}

echo "Done.\n";

exit($exitCode);

function getAuthToken(string $basicAuth, string $username, string $password): string
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HTTPHEADER,
        [
            'Authorization: Basic '.$basicAuth,
            'Content-Type: application/json'
        ]
    );

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, "http://httpd/api/oauth/v1/token");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt(
        $ch,
        CURLOPT_POSTFIELDS,
        json_encode([
            "grant_type" => "password",
            "username" => $username,
            "password" => $password
        ])
    );

    $tokenData = json_decode(curl_exec($ch), true);

    curl_close($ch);

    return $tokenData['access_token'];
}

function getData(string $authToken, string $endPoint):array
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HTTPHEADER,
    [
            'Authorization: Bearer '.$authToken,
            'Content-Type: application/json'
        ]
    );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, "http://httpd".$endPoint);

    $response = curl_exec($ch);

    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
        throw new RuntimeException(
            sprintf(
                'Unable to get product data. HTTP code %s, with content:%s',
                curl_getinfo($ch, CURLINFO_HTTP_CODE),
                $response
            )
        );
    }

    curl_close($ch);

    $rawProductData = json_decode($response, true);

    $data = [];

    foreach($rawProductData['_embedded']['items'] as $productItem) {
        unset($productItem['_links']);

        $data[] = $productItem;
    };

    return $data;
}

function sendData(string $authToken, string $endPoint, array $data, int $parallelCount)
{
    $patchData = "";

    foreach($data as $item) {
        $patchData .= json_encode($item)."\n";
    }

    $mh = curl_multi_init();

    $curlHandles = [];

    for($i = 0; $i < $parallelCount; $i++) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER,
        [
                'Authorization: Bearer '.$authToken,
                'Content-Type: application/vnd.akeneo.collection+json'
            ]
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, "http://httpd".$endPoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $patchData);

        $curlHandles[] = $ch;
    }

    foreach ($curlHandles as $curlHandle) {
        curl_multi_add_handle($mh, $curlHandle);
    }

    do {
        $status = curl_multi_exec($mh, $active);
        if ($active) {
            curl_multi_select($mh);
        }
    } while ($active && $status == CURLM_OK);


    foreach ($curlHandles as $curlHandle) {
        $rawPatchResult = curl_multi_getcontent($curlHandle);

        if (curl_getinfo($curlHandle, CURLINFO_HTTP_CODE) !== 200) {
            throw new RuntimeException(
                sprintf(
                    'Unable to send product data. HTTP code %s, with content:%s',
                    curl_getinfo($curlHandle, CURLINFO_HTTP_CODE),
                    $rawPatchResult
                )
            );
        }

        $patchResultLines = explode("\n",$rawPatchResult);

        foreach($patchResultLines as $patchResultLine) {
            $patchResult = json_decode($patchResultLine, true);

            if (null === $patchResult) {
                throw new RuntimeException(
                    sprintf(
                        'Unable to decode response line:%s. Full response was:%s.',
                        $patchResultLine,
                        $rawPatchResult
                    )
                );
            }

            if ($patchResult['status_code'] === 500) {
                throw new RuntimeException(
                    sprintf(
                        'Product in server error. Line content:%s. Full response was:%s.',
                        $patchResultLine,
                        $rawPatchResult
                    )
                );
            }
        }
        curl_multi_remove_handle($mh, $curlHandle);
    }

    curl_multi_close($mh);
}
