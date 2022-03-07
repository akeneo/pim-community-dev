# Unit tests

Run all the Onboarder Serenity unit backend tests:
```
PIM_CONTEXT=onboarder-serenity make unit-back
```

Run a single Onboarder Serenity unit backend test:
```
PIM_CONTEXT=onboarder-serenity make unit-back ARGS="path/to/the/test/from/ee-root-directory"
```

# Integration tests

Run all the Onboarder Serenity integration tests:
```
PIM_CONTEXT=onboarder-serenity make integration
```

Run a single Onboarder Serenity integration test:
```
PIM_CONTEXT=onboarder-serenity make integration ARGS="--filter <class_name_or_method_test_name>"
```
