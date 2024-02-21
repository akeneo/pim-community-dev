describe('edit product sanity check', () => {
  it('User can enrich the first product of the products grid', () => {
    cy.login('admin', 'admin');
    cy.goToProductsGrid();
    cy.selectFirstProductInDatagrid();
    cy.findFirstTextField().clear().type('updated value');
    cy.saveProduct();
    cy.reloadProduct();
    cy.findFirstTextField().should('have.value', 'updated value');
  });
});
