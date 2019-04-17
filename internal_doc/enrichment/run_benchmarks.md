# Benchmarks

It is really important to take care of the non regression of performances.

That's why we made a script available here `bin/benchmark_product_api.sh`

Caution: the script only work through docker and relies on our current stack.
It uses the `akeneo/data-generator` and the `akeneo/benchmark-api` docker images.

This script uses the reference catalog in order to benchmark the API performances.
The catalog used is available here `tests/benchmarks/product_api_catalog.yml`

If you just want to test the script, you can use `bin/benchmark_product_api.sh tests/benchmarks/test_product_api_catalog.yml`

## How to use it ?

You need to have installed your PIM with docker prior to launch the benchmarks.

`$ bin/benchmark_product_api.sh`

It displays the average speed on GET, CREATE, and UPDATE products through the API.

That's why you should launch the benchmark before and after any changes you make on the PIM.
