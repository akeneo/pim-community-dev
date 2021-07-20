describe('edit product sanity check', () => {
  it('User can enrich the first product of the products grid', () => {
    cy.login('adminakeneo', 'adminakeneo');
    cy.goToProductsGrid();
    cy.selectFirstProductInDatagrid();
    cy.findFirstTextField()
      .clear()
      .type('updated value');
    cy.saveProduct();
    cy.reload();
    cy.findFirstTextField().should('have.value', 'updated value');
  });
});
