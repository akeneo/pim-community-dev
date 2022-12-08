# Unit tests

## Back
Run all the Supplier Portal unit backend tests:
```
PIM_CONTEXT=supplier-portal make unit-back
```

Run a single Supplier Portal unit backend test for the retailer app:
```
PIM_CONTEXT=supplier-portal make unit-back-retailer ARGS="path/to/the/test/from/ee-root-directory"
```

Run a single Supplier Portal unit backend test for the supplier app:
```
PIM_CONTEXT=supplier-portal make unit-back-supplier ARGS="path/to/the/test/from/ee-root-directory"
```

## Front

Run all the Supplier Portal unit front tests:
```
PIM_CONTEXT=supplier-portal make unit-front
```

Watch the Supplier Portal unit front tests for the retailer app:
```
PIM_CONTEXT=supplier-portal make unit-front-watch-retailer
```

Watch the Supplier Portal unit front tests for the supplier app:
```
PIM_CONTEXT=supplier-portal make unit-front-watch-supplier
```

# Integration tests

Run all the Supplier Portal integration tests:
```
PIM_CONTEXT=supplier-portal make integration-back
```

Run a single Supplier Portal integration test for the retailer app:
```
PIM_CONTEXT=supplier-portal make integration-back-retailer ARGS="--filter <class_name_or_method_test_name>"
```

Run a single Supplier Portal integration test for the supplier app:
```
PIM_CONTEXT=supplier-portal make integration-back-supplier ARGS="--filter <class_name_or_method_test_name>"
```

# Acceptance tests

Run all the Supplier Portal acceptance tests:
```
PIM_CONTEXT=supplier-portal make acceptance-back
```

Run a single Supplier Portal acceptance test for the retailer app:
```
PIM_CONTEXT=supplier-portal make acceptance-back-retailer ARGS="path/to/the/test/from/ee-root-directory"
```

Run a single Supplier Portal acceptance test for the supplier app:
```
PIM_CONTEXT=supplier-portal make acceptance-back-supplier ARGS="path/to/the/test/from/ee-root-directory"
```
