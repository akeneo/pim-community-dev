describe('go to the tailored export tab', () => {
  it('It can click on the button', () => {
    cy.login();

    cy.goToExports();

    cy.findByText(/create export profile/i).click();

    cy.findByLabelText('Code (required)').type('tailoredexportprofile' + new Date().getUTCMilliseconds());
    cy.findByLabelText('Label (required)').type('Tailored Export Profile');
    cy.findByTitle('Job').click().type('product export in csv{enter}');

    cy.findByText('Save').click();
    cy.findByText('Columns').click();
    cy.findByText('Edit: test! cool').click();

    cy.findByText('Edit: test! nice').should('exist');
  });
});
