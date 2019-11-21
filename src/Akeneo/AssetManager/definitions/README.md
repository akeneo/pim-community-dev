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

# Steps to write new JSON Schema files

## 1) Create dedicated .json response files for integration tests
First you need to move the shared "response" files located in `src/Akeneo/AssetManager/tests/shared/responses` to dedicated front and back test folders.
You simply need to **copy/paste theme** to:
- `src/Akeneo/AssetManager/tests/front/integration/responses/` for the frontend
- `src/Akeneo/AssetManager/tests/back/Integration/Resources/responses/` for the backend

Then **remove them** from their initial folder (`shared/responses`).

## 2) Write a JSON Schema file for those responses
The response .json files you copied now need to be validated with the same JSON Schema.
As the **JSON Schema will validate both frontend & backend response files**, they are located in the `src/Akeneo/AssetManager/tests/shared/schemas` folder.

The JSON Schema file needs to be **in the same hierarchy than the .json responses it's validating.**

For example:
If the response files you want to write the JSON Schema for are located in: 
```text
tests/front/integration/responses/Attribute/ListDetails
tests/back/Integration/Resources/responses/Attribute/ListDetails
```
You'll need to put their `schema.json` file in `tests/shared/schemas/Attribute/ListDetails`

## 3) Extract part of the JSON Schema to new JSON Schema definition
Now that we have the JSON Schema to validate response json files, we can extract definitions of the entities in dedicated JSON Schema files.
The new definitions JSON Schema should be located in the `src/Akeneo/AssetManager/definitions` folder, named `<entity_name>.schema.json`.

## 4) Use new definitions where it's needed
Let say you have created the brand new `attribute.schema.json` definition, you now need to "include" this definition in every JSON Schema that are using an Attribute structure.

To do this, browse every JSON Schema we have:
- In the `src/Akeneo/AssetManager/tests/shared/schemas` folder
- But also in the `src/Akeneo/AssetManager/definitions` folder itself

And where we use an attribute, simply replace it by the ref of the new JSON Schema using the `$ref` keyword followed by the path to the JSON Schema file:
 
```json
{
    "my_attribute": {"$ref":  "src/Akeneo/AssetManager/definitions/attribute.schema.json"}
}
```

## 5) Test
Run these commands to quickly check if you fucked up:
```text
yarn generate-asset-manager-models
make asset-manager-static-back
```

Then of course, CI time!
