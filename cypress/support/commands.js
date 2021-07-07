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
  cy.visit('/user/login');
  cy.get('input[name="_username"]').type(username);
  cy.get('input[name="_password"]').type(password);
  cy.findByRole('button', 'Login').click();
});

Cypress.Commands.add('goToProductsGrid', () => {
  cy.findByRole('menuitem', {name: 'Activity'}).should('has.class', 'active');

  cy.findByText('Products').click();

  cy.intercept('/datagrid/product-grid*').as('productDatagrid');
  cy.intercept('/datagrid_view/rest/product-grid/default*').as('productDatagridViews');
  cy.wait('@productDatagridViews');
  cy.wait('@productDatagrid');
});

Cypress.Commands.add('selectFirstProductInDatagrid', () => {
  cy.findAllByRole('row').eq(1).click();

  cy.intercept('GET', '/enrich/product/rest/*').as('getProduct');
  cy.wait('@getProduct');
});

Cypress.Commands.add('saveProduct', () => {
  cy.intercept('POST', '/enrich/product/rest/*').as('saveProduct');

  cy.findByText('Save').click();

  cy.wait('@saveProduct');
});

Cypress.Commands.add('updateField', (label, value) => {
  cy.findByLabelText(label).clear().type(value);
});
