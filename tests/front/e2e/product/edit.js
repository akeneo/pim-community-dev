describe('edit product sanity check', () => {
  it('User can enrich the first product of the products grid', () => {
    cy.login('adminakeneo', 'Q7sKB5xP2ttc5KnqFPOF1BrOkTRSulmEj528BpJzbDcLbYSHU1');
    cy.goToProductsGrid();
    cy.selectFirstProductInDatagrid();
    cy.findFirstTextField()
      .clear()
      .type('updated value');
    cy.saveProduct();
    cy.reloadProduct();
    cy.findFirstTextField().should('have.value', 'updated value');
  });
});
