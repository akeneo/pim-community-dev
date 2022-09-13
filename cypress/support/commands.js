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
  cy.findByRole('button', {name: "Login"}).click();
});

Cypress.Commands.add('goToProductsGrid', () => {
  cy.findByRole('menuitem', {name: 'Activity'}).should('has.class', 'active');

  cy.findByText('Products').click();

  cy.intercept('/datagrid/product-grid*').as('productDatagrid');
  cy.intercept('/datagrid_view/rest/product-grid/default*').as('productDatagridViews');
  cy.wait('@productDatagridViews');
  cy.wait('@productDatagrid');

  // switch to the "ungrouped" view to have only products
  cy.get('.search-zone').find('div[data-type="grouped-variant"]').click();
  cy.get('.search-zone').find('span[data-value="product"]').click();

  // Wait for loading mask
  cy.get('.AknLoadingMask').should('be.visible');

  // Wait for XHR completion
  cy.wait('@productDatagrid');

  // Wait for loading mask deletion
  cy.get('.AknLoadingMask').should('not.be.visible');

  // Wait for change in page title to be sure DOM is ready
  cy.get('.AknTitleContainer-title div').invoke('text').should('not.contains', "product models");
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

Cypress.Commands.add('reloadProduct', () => {
  cy.reload();
  cy.intercept('GET', /\/enrich\/product\/rest\/.*/).as('getProduct');
  cy.intercept('GET', /\/configuration\/rest\/.*/).as('configuration');
  cy.wait('@getProduct');
  cy.wait('@configuration');
  cy.findFirstTextField();
});

Cypress.Commands.add('updateField', (label, value) => {
  cy.findByLabelText(label).clear().type(value);
});

Cypress.Commands.add('findFirstTextField', () => {
  return cy.get('.edit-form').find('.akeneo-text-field input:not([disabled]).AknTextField').first();
});
