# Apps

Pure React stack, no legacy allowed (Backbone, JQuery, ...)

> `package.json` is provided for the scripts and the definitions of the package dependencies (even if we only use those installed by the top level `package.json` of the CE).

## Project structure

-   `application/`
    -   `component/`
        -   `app/` UI components (buttons, ...)
        -   `shared/` Shared/service components (translate, ...)
    -   `context/` React contexts
    -   `service/`
        -   `shared/` Interface for shared services (translation, security, ...)

## Tests

- [Snapshot testing](https://jestjs.io/docs/en/snapshot-testing)
