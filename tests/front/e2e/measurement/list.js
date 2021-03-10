describe('list measurment families', () => {
  it('It can display measurement families', () => {
    cy.login().visit('/#/configuration/measurement')

    cy.findByText('23 Measurement families')
    .should('exist')
      // .get('._2S_Gj6clvtEi-dZqCLelKb > :nth-child(3)')
      // .click()
      // .get('._1yUJ9HTWYf2v-MMhAEVCAn > :nth-child(4)')
      // .click()
      // .get('._2S_Gj6clvtEi-dZqCLelKb > :nth-child(4)')
      // .click()
      // .get('._1yUJ9HTWYf2v-MMhAEVCAn > :nth-child(5)')
      // .click()
      // .get('.mNQM6vIr72uG0YPP56ow5')
      // .should('have.text', '3')
  })
})
