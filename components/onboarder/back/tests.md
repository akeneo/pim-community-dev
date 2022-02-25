# Unit tests

Run all the Onboarder Serenity unit tests:
```
PIM_CONTEXT=onboarder-serenity make onboarder-unit-tests
```

Run a single Onboarder Serenity unit test:
```
PIM_CONTEXT=onboarder-serenity make onboarder-unit-tests IO=" path/to/the/test"
```

# Integration tests

Run all the Onboarder Serenity integration tests:
```
PIM_CONTEXT=onboarder-serenity make onboarder-integration-tests
```

Run a single Onboarder Serenity integration test:
```
PIM_CONTEXT=onboarder-serenity make onboarder-integration-tests IO=" --filter <class_name_or_method_test_name>"
```
