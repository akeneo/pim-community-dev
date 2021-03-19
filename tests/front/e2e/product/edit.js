describe('edit product', () => {
//   it('It can go to the products grid', () => {
//     cy.login();

//     cy.goToProductsGridWait();
//     // cy.goToProductsGridFindActivityItem();
//     // cy.goToProductsGridUsingUrl();
//   });

  it('It can go to the first product of the products grid', () => {
    cy.login();

    cy.goToProductsGridFindActivityItem();

    cy.findAllByRole('row').eq(1).click();

    cy.findByText('Last update').should('exist');
  });
});
