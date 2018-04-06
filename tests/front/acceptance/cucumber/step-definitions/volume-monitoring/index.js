module.exports = async function(cucumber) {
    const { Given, Then, When } = cucumber;
    const assert = require('assert');

    // @TODO - to complete in PIM-7211
    Given('a family with {int} attributes', async function (int) {
        assert(int);
    });

    Given('the limit of the number of attributes per family is set to {int}', async function (int) {
        assert(int);
    });

    When('the administrator user asks for the catalog volume monitoring report', async function () {
        assert(true);
    });

    Then('the report warns the users that the number of attributes per family is high', async function () {
        assert(true);
    });

    Then('the report returns that the average number of attributes per family is {int}', async function (int) {
        assert(int);
    });

    Then('the report returns that the maximum number of attributes per family is {int}', function (int) {
        assert(int);
    });
};
