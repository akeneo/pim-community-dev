# UCS Tenant Deletion

## How to test and develop in local?

Install all the prerequisites defined in the `package.json`:
```
$ npm install
```

Start the development server:
```
$ gcloud auth application-default login
$ npm start
```

Execute the test to delete the tenant:
```
$ TENANT_NAME=test mocha tests/deleteTenant.system.http.test.js --timeout 10000
```
