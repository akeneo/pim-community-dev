# Writing frontend tests

## Acceptance

### How the tests are set up

The frontend acceptance tests are run with [cucumber-js]() for the scenarios and [puppeteer]() for executing the tests. We run the tests without any backend using fixture data. To achieve this, we:

#### 1. Make a dump of the form extensions using the FormExtensionProvider

In our acceptance test definitions we can capture network requests called by the app and replace them with custom fixtures. When the app calls the form extensions endpoint we will return this dump. To create this dump, run `bin/console pim:installer:dump-extensions` and it will output a file at `web/test_dist/extensions.json`. 

#### 2. Use webpack to create a test version of our frontend bundle
For this step, we build the app using custom entry points (`index.html` and `index.js` in `webpack/test/templates`). This allows us to replace the normal `index.html.twig` from the EnrichBundle and eventually only render the views that we want instead of the whole app. 

We have a custom webpack test config `webpack-test.config.js` that uses the custom entry points and outputs all the built javascript inline inside the `index.html`. 

To create the test version of the PIM you can run `yarn run webpack-test`. This will generate in the `web/test_dist` folder a `index.html` file that includes all the frontend code (excluding the CSS). 

#### 3. Running the tests

Just like the other frontend commands, we use scripts in package.json to run the acceptance tests.

There are three commands that can be run: 
- `yarn run acceptance` - This runs the tests normally, using headless chrome (The CI uses this)
- `yarn run acceptance-debug` - This runs the tests in debug mode, opening a chrome instance and keeping the process open after scenarios are run
- `yarn run generate-report` - This generates a html report of the last test

> Note: If you want to run inspect or add breakpoints in the acceptance tests or step definitions you can follow these steps:
    - Add a breakpoint somewhere in your step definition with `debugger;`
    - Run `node --inspect-brk  node_modules/.bin/cucumber-js -r ./webpack/test/acceptance/run-steps.js -r ./tests/front/acceptance/cucumber ./tests/front/acceptance/features/`
    - Go to `chrome://inspect` in Chrome and click on the target `node_modules/.bin/cucumber-js`. If you don't see it, you can click on `Open dedicated DevTools for Node` instead. An inspector window will open. 
    - Go to the sources tab in the inspector and click the play icon, you should now be able to walk through the steps

The important files and folders are:
- `run-steps.js` - This file gathers all the step definitions from `tests/front/acceptance/cucumber/step-definitions`
- `world.js` - This is the main file the cucumber-js (name is cucumber convention) uses to execute the scenarios. Here we do all the setup:
    - Before each scenario, load the browser using `puppeteer` (with some debug options)
    - Start the interception of requests
    - Capture console log messages from the browser
    - Set up the JSON responses for the user and form extensions endpoints
    - After each scenario capture and report the success or failure
    - Generate a screenshot for the scenario failure
    - Close the browser
- `generate-report.js` - After the tests are run, this file can take the test output json from `web/test_dist/acceptance-js.json` and create a html report. 

> Note: Cucumber normally handles the auto-discovery of steps but this feature doesn't work with the EE. Cucumber unfortunately doesn't support executing step definitions from CE using the cucumber instance from inside the EE node_modules. So we manually gather the step definition files and pass in the cucumber instance.

In the end, you only have to worry about running these commands:
1. `bin/console pim:installer:dump-extensions` (once)
2. `yarn run webpack-test` (once)
3. `yarn run acceptance`

### How to write frontend acceptance tests

Writing the acceptance tests in cucumber-js is not so different from the behat/mink/selenium setup. The test setup is composed of:

#### 1. Feature files in `tests/front/acceptance/features`

```gherkin
Feature: Displaying the association type edit form
    Scenario: Display the edit form
        Given the locales "en_US, fr_FR"
        And the edit form for association type "Cross sell" is displayed
        Then the title of the page should be "Cross sell"
        And the association type code should be "X_SELL"
        And the code field should be disabled # undefined step

```

If you execute `yarn run webpack` with a feature file that has undefined steps, cucumber will report it and generate the method:
```bash
1) Scenario: Display the edit form # tests/front/acceptance/features/association-type/edit.feature:2
   ✔ Before # tests/front/acceptance/cucumber/world.js:11
   ✔ Given the locales "en_US, fr_FR" # tests/front/acceptance/cucumber/step-definitions/structure/locales.js:6
   ✔ And the edit form for association type "Cross sell" is displayed # tests/front/acceptance/cucumber/step-definitions/association-type/edit.js:6
   ✔ Then the title of the page should be "Cross sell" # tests/front/acceptance/cucumber/step-definitions/page.js:5
   ✔ And the association type code should be "X_SELL" # tests/front/acceptance/cucumber/step-definitions/association-type/edit.js:29
   ? And the code field should be disabled
       Undefined. Implement with the following snippet:

         Then('the code field should be disabled', function (callback) {
           // Write code here that turns the phrase above into concrete actions
           callback(null, 'pending');
         });
       
   ✔ After # tests/front/acceptance/cucumber/world.js:60

3 scenarios (1 undefined, 2 passed)
5 steps (1 undefined, 4 passed)
```

#### 2. Step definitions in `tests/front/acceptance/cucumber/step-definitions`

Once you've written the feature you need to define the steps. For example:

```javascript
// Always wrap the definition in a module export with cucumber as a parameter
module.exports = function(cucumber) {
    const { Then } = cucumber;
    const assert = require('assert');

    // Define the step (the 'Then' is arbitrary - Then/When/Given are all aliases for the defineStep method of cucumber)
    Then('the title of the page should be {string}', async function (string) {
        // Use puppeteer to get the page title element
        const titleElement = await this.page.waitForSelector('.AknTitleContainer-title');

        // Get the textContent DOM property from the title element
        const pageTitle = await (await titleElement.getProperty('textContent')).jsonValue();

        // Assert that the page title is what we expect
        assert.equal(pageTitle.trim(), string);
    });
};

```

The step definitions use [cucumber-js](https://github.com/cucumber/cucumber-js) to define the step, [puppeteer](https://github.com/GoogleChrome/puppeteer) to access the page object, and the NodeJs [assert](https://nodejs.org/api/assert.html) library to make the assertions. We also use [async/await](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Statements/async_function) instead of nested callbacks to make the code more readable as most of the puppeteer page methods are not synchronous. 

#### 3. Methods to generate entity fixtures in `tests/front/acceptance/cucumber/factory`

In almost every case, you will have to generate some dummy data for your scenario. Let's take this example: 

```javascript
module.exports = function(cucumber) {
    const { Given } = cucumber;
    const createLocale = require('../../factory/locale');
    const  { answerJson, csvToArray } = require('../../tools');

    Given('the locales {string}', async function(csvLocaleCodes) {
        const locales = csvToArray(csvLocaleCodes).map(localeCode => createLocale({code: localeCode}));

        this.page.on('request', request => {
            if (request.url().includes('/configuration/locale/rest')) {
                answerJson(request, locales);
            }
        });
    });
};
```

In this step definition, we want to set up the locales for the `Given the locales en_US, fr_FR` step. 

We generate the response with the `createLocale` factory in `tests/front/acceptance/cucumber/factory/locale.js`. This lets us answer the request with our own generated data. Every factory has default dummy data that can be overridden. To view the available factory methods you can check `tests/front/acceptance/cucumber/factory`

Once you have your step definition and feature file, you can execute `yarn run acceptance` to run the tests. 
