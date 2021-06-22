describe('edit product sanity check', () => {
  it('User can enrich the first product of the products grid', () => {
    cy.login('admin', 'admin');
    cy.goToProductsGrid();
    cy.selectFirstProductInDatagrid();
    cy.updateField('Name', 'updated product');
    cy.saveProduct();
    cy.reload();
    cy.findByDisplayValue('updated product').should('exist');
  });
});
