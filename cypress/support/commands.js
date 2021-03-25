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

Cypress.Commands.add('login', () => {
  cy.visit('/user/login');
  cy.findByLabelText('Username or Email').type('admin');

  cy.findByLabelText('Password').type('admin');

  // cy.intercept('/configuration/feature-flags').as('featureFlag');
  // cy.intercept('/rest/user').as('user');
  cy.intercept('/analytics/collect_data').as('analytics');

  cy.findByRole('button', 'Login').click();

  // cy.wait('@featureFlag'); //4000ms
  // cy.wait('@user'); //4000ms
  cy.wait('@analytics', 20000); //4000ms
});

Cypress.Commands.add('goToProductsGridWait', () => {
  cy.findAllByText(/dashboard/i);
  cy.wait(2000);

  cy.intercept('/datagrid/product-grid*').as('productDatagrid')
  cy.intercept('/datagrid_view/rest/product-grid/default*').as('productDatagridViews')

  cy.findByText('Products').click();

  cy.wait('@productDatagridViews')
  cy.wait('@productDatagrid')
});

Cypress.Commands.add('goToProductsGridFindActivityItem', () => {
  //We should rework the HTML to have proper role/aria selectors
  cy.findByText('Activity').should('have.class', 'AknHeader-menuItem--active');

  cy.findByText('Products').click();

  cy.intercept('/datagrid/product-grid*').as('productDatagrid')
  cy.intercept('/datagrid_view/rest/product-grid/default*').as('productDatagridViews')
  cy.wait('@productDatagridViews')
  cy.wait('@productDatagrid')
});

Cypress.Commands.add('goToProductsGridUsingUrl', () => {
  cy.intercept('/datagrid/product-grid*').as('productDatagrid')
  cy.intercept('/datagrid_view/rest/product-grid/default*').as('productDatagridViews')

  cy.visit('/#/enrich/product/');

  cy.wait('@productDatagridViews')
  cy.wait('@productDatagrid')
});

Cypress.Commands.add('selectFirstProductInDatagrid', () => {
  cy.findAllByRole('row').eq(1).click();

  cy.intercept('GET', '/enrich/product/rest/*').as('getProduct')
  cy.wait('@getProduct')
})

Cypress.Commands.add('saveProduct', () => {
  cy.intercept('POST', '/enrich/product/rest/*').as('saveProduct')

  cy.findByText('Save').click();

  cy.wait('@saveProduct')
})

Cypress.Commands.add('updateField', (label, value) => {
  cy.findByLabelText(label).clear().type(value)
})
