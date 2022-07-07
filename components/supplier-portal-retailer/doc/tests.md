# Unit tests

## Back
Run all the Supplier Portal unit backend tests:
```
PIM_CONTEXT=supplier-portal make unit-back
```

Run a single Supplier Portal unit backend test:
```
PIM_CONTEXT=supplier-portal make unit-back ARGS="path/to/the/test/from/ee-root-directory"
```

## Front

Run all the Supplier Portal unit front tests:
```
PIM_CONTEXT=supplier-portal make unit-front
```

Watch the Supplier Portal unit front tests:
```
docker-compose -f ./docker-compose.yml -f ./docker-compose.override.yml run --rm -e YARN_REGISTRY -e PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=1 -e PUPPETEER_EXECUTABLE_PATH=/usr/bin/google-chrome node yarn --cwd=components/supplier-portal-retailer/front test:unit:watch
```

# Integration tests

Run all the Supplier Portal integration tests:
```
PIM_CONTEXT=supplier-portal make integration-back
```

Run a single Supplier Portal integration test:
```
PIM_CONTEXT=supplier-portal make integration-back ARGS="--filter <class_name_or_method_test_name>"
```

# Acceptance tests

Run all the Supplier Portal acceptance tests:
```
PIM_CONTEXT=supplier-portal make acceptance-back
```

Run a single Supplier Portal acceptance test:
```
PIM_CONTEXT=supplier-portal make acceptance-back ARGS="path/to/the/test/from/ee-root-directory"
```
