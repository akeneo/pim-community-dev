describe('List of Category trees', () => {
  before(() => {
    cy.login('julia', 'julia');

    cy.visit('/#/enrich/product-category-tree/');
  });

  it('creates a category tree', () => {
    cy.findByText(/Create/).click();

    cy.findByRole('dialog').as('form').should('be.visible');
    cy.updateField('Code', 'new_category_tree');

    cy.intercept('POST', '/enrich/product-category-tree/create').as('createCategoryTree');
    cy.get('@form')
      .findByText(/Create/i)
      .click();
    cy.wait('@createCategoryTree');

    cy.findByRole('status')
      .as('notification')
      .findByText(/new_category_tree/i)
      .should('exist');

    cy.findByRole('row', {name: /new_category_tree/i})
      .as('categoryTree')
      .should('exist');
  });

  it('deletes a category tree', () => {
    cy.findByRole('row', {name: /new_category_tree/i})
      .as('categoryTree')
      .should('exist');
    cy.get('@categoryTree')
      .trigger('mouseover')
      .findByRole('button', {name: /Delete/i})
      .click();

    cy.findByRole('dialog').as('confirm').should('be.visible');

    cy.intercept('DELETE', '/enrich/product-category-tree/*/remove').as('removeCategoryTree');
    cy.get('@confirm')
      .findByRole('button', {name: /Delete/i})
      .click();
    cy.wait('@removeCategoryTree');

    cy.findByRole('row', {name: /new_category_tree/i}).should('not.exist');
  });
});
