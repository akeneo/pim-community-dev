# Writing frontend tests

## Acceptance

### How the tests are set up

The frontend acceptance tests are run with [cucumber-js]() for the scenarios and [puppeteer]() for executing the tests in headless chrome. We run the tests without any backend using fixture data. To achieve this, we:

#### 1. Make a dump of the form extensions using the FormExtensionProvider

In our acceptance test definitions we can capture network requests called by the app and replace them with custom fixtures. When the app calls the form extensions endpoint we will return this dump. To create this dump, run `bin/console pim:installer:dump-extensions` and it will output a file at `public/test_dist/extensions.json`.

#### 2. Use webpack to create a test version of our frontend bundle
For this step, we build the app using custom entry points (`index.html` and `index.js` in `webpack/test/templates`). This allows us to replace the normal `index.html.twig` from the EnrichBundle and eventually only render the views that we want instead of the whole app.

We have a custom webpack test config `webpack-test.config.js` that uses the custom entry points and outputs all the built javascript inline inside the `index.html`.

To create the test version of the PIM you can run `yarn run webpack-test`. This will generate in the `public/test_dist` folder a `index.html` file that includes all the frontend code (excluding the CSS).

#### 3. Running the tests

Just like the other frontend commands, we use scripts in package.json to run the acceptance tests.

There are three commands that can be run:
- `yarn run acceptance [feature]` - This runs the tests normally, using headless chrome (The CI uses this)
- `yarn run generate-report` - This generates a html report of the last test

> Note: If you want to run inspect or add breakpoints in the acceptance tests or step definitions you can follow these steps:
    - Add a breakpoint somewhere in your step definition with `debugger;`
    - Run `node --inspect-brk node_modules/.bin/cucumber-js --tags @acceptance-front -r ./frontend/test/acceptance/run-steps.js -r ./tests/front/acceptance/cucumber ./tests/features/`
    - Go to `chrome://inspect` in Chrome and click on the target `node_modules/.bin/cucumber-js`. If you don't see it, you can click on `Open dedicated DevTools for Node` instead. An inspector window will open.
    - Go to the sources tab in the inspector and click the play icon, you should now be able to walk through the steps

The important files and folders are:
- `run-steps.js` - This file gathers all the step definitions from `tests/front/acceptance/cucumber/step-definitions`
- `world.js` - This is the main file the cucumber-js uses to execute the scenarios. Here we do all the setup:
    - Before each scenario, load the browser using `puppeteer` (with some debug options)
    - Start the interception of requests
    - Capture console log messages from the browser
    - Set up the JSON responses for the user and form extensions endpoints
    - After each scenario capture and report the success or failure
    - Generate a screenshot for the scenario failure
    - Close the browser
- `generate-report.js` - After the tests are run, this file can take the test output json from `public/test_dist/acceptance-js.json` and create a html report.

> Note: Cucumber normally handles the auto-discovery of steps but this feature doesn't work with the EE. Cucumber unfortunately doesn't support executing step definitions from CE using the cucumber executable from inside the EE node_modules. So we manually gather the step definition files and pass in the cucumber instance.

In the end, you only have to worry about running these commands:
1. `bin/console pim:installer:dump-extensions` (once)
2. `yarn run webpack-test` (once)
3. `yarn run acceptance [feature]`

### How to write frontend acceptance tests

Writing the acceptance tests for cucumber-js is not so different from the behat/mink/selenium. The test setup is composed of:

#### 1. Feature files in `./tests/features/`
```gherkin
Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of attributes per family

  @acceptance @acceptance-front
  Scenario: Monitor the number of attributes per family
    Given a family with 10 attributes
    And a family with 4 attributes
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the average number of attributes per family is 7
    And the report returns that the maximum number of attributes per family is 10
```

> Note: You must tag your features with `@acceptance-front` as Cucumber will only run those features with this tag (behat can also run the same feature file).

If you execute `yarn run webpack` with a feature file that has undefined steps, cucumber will report it and generate the method:
```bash
1) Scenario: Monitor the number of attributes per family # tests/features/volume-monitoring/monitor_attribute_per_family_volume.feature:7
   ✔ Before # tests/front/acceptance/cucumber/world.js:11
   ✔ Given a family with 10 attributes # tests/front/acceptance/cucumber/step-definitions/volume-monitoring/index.js:6
   ✔ And a family with 4 attributes # tests/front/acceptance/cucumber/step-definitions/volume-monitoring/index.js:6
   ? When the administrator user asks for the catalog volume monitoring report
       Undefined. Implement with the following snippet:

         When('the administrator user asks for the catalog volume monitoring report', function (callback) {
           // Write code here that turns the phrase above into concrete actions
           callback(null, 'pending');
         });

   - Then the report returns that the average number of attributes per family is 7 # tests/front/acceptance/cucumber/step-definitions/volume-monitoring/index.js:22
   - And the report returns that the maximum number of attributes per family is 10 # tests/front/acceptance/cucumber/step-definitions/volume-monitoring/index.js:26
   ✔ After # tests/front/acceptance/cucumber/world.js:57
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

Once you have your step definition and feature file, you can execute `yarn run acceptance [feature]` to run the tests.

## Launching tests

To launch all the tests:

> `yarn run acceptance ./tests/features/`

> Note: Running `yarn run acceptance` without specifying the folder, scenario or glob (see: [Running features in Cucumber](https://github.com/cucumber/cucumber-js/blob/master/docs/cli.md#running-specific-features)) will cause it to run every scenario in any `/feature` folder in the repository !

To launch a single test:

> `yarn run acceptance ./tests/features/volume-monitoring/monitor_attribute_per_family_volume.feature`

To launch a single scenario:

> `yarn run acceptance ./tests/features/volume-monitoring/monitor_attribute_per_family_volume.feature:7`

To launch the tests with the browser open:

> `yarn run acceptance ./tests/features/ --world-parameters='{"debug": true}'`

To launch the tests and generate a html report:

> `yarn run acceptance-report`

Note: If you are using yarn <1.0 you must add `--` before the feature name:

> `yarn run acceptance -- ./tests/features/volume-monitoring/monitor_attribute_per_family_volume.feature:2`

You can also run the tests with two additional options -
- `RANDOM_LATENCY` - (default `true`)
- `MAX_RANDOM_LATENCY_MS` - (default `1000`)

These options can add a degree of randomness to your tests for loading the page and responding to JSON requests with custom data. For example, we can launch the test like this:

> `RANDOM_LATENCY=true MAX_RANDOM_LATENCY_MS=2000 yarn run acceptance [feature]`

```javascript
    const  { answerJson, csvToArray } = require('../../tools');

    // A step definition that hijacks the request for locales and returns custom locales
    Given('the locales {string}', async function(csvLocaleCodes) {
        const locales = csvToArray(csvLocaleCodes).map(localeCode => createLocale(localeCode));

        // Hijack the page request
        this.page.on('request', request => {
            if (request.url().includes('/configuration/locale/rest')) {
                // Answer the request with our custom data
                answerJson(request, locales);
            }
        });
    });
```

Within the `answerJson` method we use the `RANDOM_LATENCY` and `MAX_RANDOM_LATENCY_MS` options to define the maximum time delay that the method will take to respond to the request.
