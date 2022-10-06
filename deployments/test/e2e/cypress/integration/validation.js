if (Cypress.env('DEPLOYMENT_TEST')) {
  describe('Validate production ' + Cypress.env('PRODUCT_TYPE') + ' instance', () => {
    // Keep session between tests
    Cypress.Cookies.defaults({
      preserve: 'BAPID',
    });

    beforeEach(() => {
      cy.viewport(1920, 1200);
    });

    it('User can login', () => {
      cy.login(Cypress.env('PIM_WEB_LOGIN'), Cypress.env('PIM_WEB_PASSWORD'));
    });

    // Check version
    if (Cypress.env('PIM_VERSION')) {
      it('Shown PIM version is version requested', () => {
        cy.validateVersionEqualsTo(Cypress.env('PIM_VERSION'));
      });
    }

    it('User can show product grid', () => {
      cy.goToProductsGrid();
    });

    if (Cypress.env('CHECK_IMAGE_LOADING')) {
      it('Images load properly', () => {
        // Check image
        cy.log('Check if images are loaded');
        cy.validateImageLoading();
      });
    }

    const TS = Date.now();
    const SKU = 'deployment_validation_' + TS;

    it('User can create a product and completion increase when he update it', {defaultCommandTimeout: 30000}, () => {
      // Create Product
      cy.log('Create product ' + SKU);
      cy.createNewProduct(SKU);

      cy.log('Update product ' + SKU);
      cy.validateCompletionIncrease('Product name');

      cy.log('Delete product ' + SKU);
      cy.deleteProduct(SKU);
    });

    it('User can export a product list (single file)', {defaultCommandTimeout: 30000}, () => {
      cy.exportSingleProductList();
    });

    it('User can export a product list (multiple files)', {defaultCommandTimeout: 30000}, () => {
      cy.exportMultipleProductList();
    });

    it('User can disconnect', () => {
      cy.disconnect();
    });
  });
}
