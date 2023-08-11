# Parallel API calls test

This tool calls the Product and Product Model API in parallel,
in order to detect dysfunctions in the handling of parallel API write calls
(like database deadlocks for example).

1. Boot your PIM

`$ make pim-test̀`

2. Load the Icecat catalog

`$ APP_ENV=test O="--catalog src/Akeneo/Platform/Installer/back/src/Infrastructure/Symfony/Resources/fixtures/icecat_demo_dev" make database`
3. Create an API connection

```
$ APP_ENV=test docker-compose run php bin/console akeneo:connectivity-connection:create parallel_api_calls
Code: parallel_api_calls
Client ID: 1_6coeumqtx64owcg0oosg4k0www0g0wssswsgcw44g4csowwgck
Secret: 2pu2c79qn6yok8kcgocs4okw4wgsw4w48wwws0sww88080co0c
Username: parallel_api_calls_4268
Password: 5dde2008e
```

4. Run the test program
```
$ APP_ENV=test docker-compose run php php tests/parallel_api_calls/parallel_api_calls.php \
    -c 1_6coeumqtx64owcg0oosg4k0www0g0wssswsgcw44g4csowwgck
    -s 2pu2c79qn6yok8kcgocs4okw4wgsw4w48wwws0sww88080co0c
    -u admin -p admin -P 10
```

5. Check the output and return code of the test program.
If the return code is not equals to 0, then a server error occured during the test. Check the output for more details.
