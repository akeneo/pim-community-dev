
# Execute test for a specific instance

## Instance c3po (srnt)
````bash
LOGIN=<C3PO_LOGIN> PASSWORD=<C3PO_PASSWORD> VERSION=<C3PO_VERSION> TYPE=srnt make -C deployments/ test_deployment_e2e
````

## Instance r2d2 (grth)
````bash
LOGIN=<R2D2_LOGIN> PASSWORD=<R2D2_PASSWORD> VERSION=<R2D2_VERSION> TYPE=grth make -C deployments/ test_deployment_e2e
````

# Execute test for a specific instance

Update configuration file "config/cypress.local.json" and set its values as :
* baseUrl: (string)(default: "http://localhost:8080") URL of instance to test
* env.CHECK_IMAGE_LOADING: (boolean)(default: true) check if images are properly loaded
* env.PIM_VERSION:(string)(default "master") Version of the instance to test 
* env.PIM_WEB_LOGIN: (string)(default "admin") Login
* env.PIM_WEB_PASSWORD: (string)(default "admin") Password
* env.PRODUCT_TYPE: (string)(default "srnt") Type of product

/!\ Export step fails locally
````bash
TYPE=local make -C deployments/ test_deployment_e2e
````
## Test localhost

If you want to test your local instance, you have to add "network_mode: host" to your docker-compose