// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add("login", (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add("drag", { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add("dismiss", { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite("visit", (originalFn, url, options) => { ... })

import '@testing-library/cypress/add-commands';

Cypress.Commands.add('login', (username, password) => {
  cy.viewport(1280, 800)
  cy.visit('/user/login');
  cy.get('input[name="_username"]').type(username);
  cy.get('input[name="_password"]').type(password);
  cy.findByRole('button', 'Login').click();
});

Cypress.Commands.add('goToProductsGrid', () => {
  //We should rework the HTML to have proper role/aria selectors
  cy.get('a[href="#/dashboard"]').should('have.class', 'AknColumn-navigationLink--active');

  cy.findByText('Products').click();

  cy.intercept('/datagrid/product-grid*').as('productDatagrid');
  cy.intercept('/datagrid_view/rest/product-grid/default*').as('productDatagridViews');
  cy.wait('@productDatagridViews');
  cy.wait('@productDatagrid');

  // switch to the "ungrouped" view to have only products
  cy.get('.search-zone').find('div[data-type="grouped-variant"]').click()
  cy.get('.search-zone').find('span[data-value="product"]').click()
  cy.wait('@productDatagrid');
});

Cypress.Commands.add('selectFirstProductInDatagrid', () => {
  Cypress.on('uncaught:exception', (err, runnable, promise) => {
    // when the exception originated from an unhandled promise
    // rejection, the promise is provided as a third argument
    // you can turn off failing the test in this case
    if (promise) {
      return false
    }
    // we still want to ensure there are no other unexpected
    // errors, so we let them fail the test
  })
  cy.findAllByRole('row').eq(1).click();

  cy.intercept('GET', /\/enrich\/product\/rest\/.*/).as('getProduct');
  cy.intercept('GET', /\/configuration\/rest\/.*/).as('configuration');
  cy.wait('@getProduct');
  cy.wait('@configuration');
});

Cypress.Commands.add('saveProduct', () => {
  cy.intercept('POST', /\/enrich\/product\/rest\/.*/).as('saveProduct');

  cy.findByText('Save').click();

  cy.wait('@saveProduct');
});

Cypress.Commands.add('updateField', (label, value) => {
  cy.findByLabelText(label).clear().type(value);
});

Cypress.Commands.add('findFirstTextField', () => {
  return cy.get('.edit-form').find('.akeneo-text-field input:not([disabled]).AknTextField').first();
});
