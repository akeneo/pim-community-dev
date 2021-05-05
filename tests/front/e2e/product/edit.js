describe('edit product', () => {
  it('It can enrich the first product of the products grid', () => {
    cy.login();

    cy.goToProductsGrid();

    cy.selectFirstProductInDatagrid();

    cy.updateField('Name', 'updated product');

    cy.saveProduct();

    cy.reload();

    cy.findByDisplayValue('updated product').should('exist');
  });
});
