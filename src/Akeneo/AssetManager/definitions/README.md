## Definitions

All `*.schema.json` files in this folder are used to **describe contracts between backend & frontend** of the AssetManager bounded context.

They are used to:
1) Generate Typescript interfaces used by the frontend
2) Validate `.json` files for frontend/backend integration tests

### 1) Generate Typescript interfaces
The backend sends json data to the frontend.  
We use the `*.schema.json` to generate interface corresponding to these JSON structure.

To re-generate interfaces:
```
yarn generate-asset-manager-models 
```

### 2) Validate `.json` test files
```
(Backend Integration Tests) => (test-response.json)
                                        ↳ We need to ensure its structure

(mocked-backend-response.json) => (Frontend Integration Tests)
              ↳ We need to ensure its structure
```
As you can see, we need to ensure the structure of `.json` files for our test, to guarantee they have **the same structure**.
