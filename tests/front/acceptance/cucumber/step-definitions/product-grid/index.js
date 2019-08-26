module.exports = function(cucumber) {
  const { Given, Then } = cucumber;
  // const  { answerJson, csvToArray } = require('../../tools');

  Given('the "default" catalog configuration', function (callback) {
    callback(null, 'pending');
  });

  Given('I am logged in as "Mary"', function (callback) {
    callback(null, 'pending');
  });

  Given('the following attributes:', function (dataTable, callback) {
    callback(null, 'pending');
  });

  Given('the following products:', function (dataTable, callback) {
    callback(null, 'pending');
  });

  Given('the "book" product has the "count" attribute', function (string, string2, callback) {
    callback(null, 'pending');
  });

  Given('the "mug" product has the "rate" attribute', function (string, string2, callback) {
    callback(null, 'pending');
  });

  Given('I am on the products grid', function (callback) {
    callback(null, 'pending');
  });

  Then('the grid should contain 3 elements', function (int, callback) {
    callback(null, 'pending');
  });

  Then('I should see products postit, book and mug', function (callback) {
    callback(null, 'pending');
  });

  Then('I should be able to use the following filters:', function (dataTable, callback) {
    callback(null, 'pending');
  });
};


