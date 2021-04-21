describe('go to the tailored export tab', () => {
  it('It can click on the button', () => {
    cy.login();

    cy.goToExports();

    cy.findByText(/create export profile/i).click();

    cy.findByTitle('Code').click().type('tailoredexportprofile' + new Date().getUTCMilliseconds());
    cy.findByTitle('Label').click().type('Tailored Export Profile');

    cy.get('#s2id_job a').click()
    cy.get('.select2-input').type('product export in csv{enter}');

    cy.findByText('Save').click();
    cy.findByText('Columns').click();
    cy.findByText('Edit: test! cool').click();

    cy.findByText('Edit: test! nice').should('exist');
  });
});
