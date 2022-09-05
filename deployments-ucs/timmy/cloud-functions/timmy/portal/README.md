# UCS Request Portal

## How does it work?

Once deployed on google the function is triggered by a cloudscheduler that runs every 2 minutes. 
It will query the portal to retrieve the different tenants according to their status.  `
Then according to their status the function will call different cloud functions (creation, deletion, update) 
to dispatch the tasks. 

## How to test and develop in local?

Install all the prerequisites defined in the `package.json`:
```
$ npm install
```

Ensure mocked-portal is well deployed in `mocked-portal` namespace:

```
$ kubectl get pods -n mocked-portal
```

Configure the port-forwarding with wiremock:

```
$ export POD_NAME=$(kubectl get pods --namespace mocked-portal -l "app.kubernetes.io/name=wiremock,app.kubernetes.io/instance=mocked-portal" -o jsonpath="{.items[0].metadata.name}")
$ kubectl port-forward --namespace mocked-portal $POD_NAME 8080:9021 
```

Test if API routes are ok :

```
$ curl http://localhost:8080/auth/realms/connect/protocol/openid-connect/token -X POST
$ curl http://localhost:8080/api/v2/console/requests/pending_creation?subject_type=serenity_instance&continent=europe&environment=sandbox
```

Start the development server:
```
$ gcloud auth application-default login
$ npm start
```

Execute the test to delete the tenant:
```
$ mocha tests/requestPortal.system.http.test.js --timeout 10000
```

