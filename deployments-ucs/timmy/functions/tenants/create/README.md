# UCS Tenant Creation

## How does it work?

The function takes dynamic values from the body of the HTTP request (`req.body`).
The structure and value in the body must match the schema present in `schemas/request-body`.
If this is not the case an error is raised.

Then the body JSON object is deeply merged with a computed object by the function. 
This new merged JSON will then be used to template an ArgoCD application manifest (`templates/argocd-application.template`).

Once templated the YAML manifest is parsed into JSON format. The function sent the JSON to the ArgoCD server.
This create the ArgoCD application and therefore the new tenant.


## How to test and develop in local?

Install all the prequisites defined in packages.json:

```
$ npm install
```

Start the development server:

```
$ gcloud auth application-default login
$ npm start
```

Execute the test to create the HTTP request:

```
$ FUNCTION_URL=http://localhost:8080
$ MAILER_API_KEY=<replace>
$ mocha test/sample.system.http.test.js --timeout 10000000
```

Ensure the tenant is deleted in ArgoCD during your tests and after.

