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

  cy.findByRole('button', 'Login').click();
});

Cypress.Commands.add('goToProductsGridWait', () => {
  cy.findAllByText(/dashboard/i);
  cy.wait(2000);

  cy.findByText('Products').click();
});

Cypress.Commands.add('goToProductsGridFindActivityItem', () => {
  // We should rework the HTML to have proper role/aria selectors
  cy.findByText('Activity').should('have.class', 'AknHeader-menuItem--active');

  cy.findByText('Products').click();
});

Cypress.Commands.add('goToProductsGridUsingUrl', () => {
  cy.visit('/#/enrich/product/');
});