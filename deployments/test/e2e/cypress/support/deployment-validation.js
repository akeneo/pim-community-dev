// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add("login", (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add("drag", { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add("dismiss", { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite("visit", (originalFn, url, options) => { ... })
const path = require('path')

Cypress.Commands.add('validateVersionEqualsTo', (versionLabel) => {
    cy.findByRole('menuitem', {name: 'Activity'}).should('has.class', 'active');
    cy.intercept('/analytics/collect_data').as('getData');
    cy.wait('@getData');
    cy.get('div#container > div.view > div > div:last').invoke('text')
    .then(text => {
        cy.log("Version found : " + text);
        cy.wrap(text).should('contains', versionLabel, { message: 'Version (' + text + ') does not match ' + versionLabel});
    });
});

Cypress.Commands.add('validateImageLoading', () => {
    cy.get('img.AknGrid-image').not('[src="/media/show/undefined/thumbnail_small"]').first()
        .scrollIntoView()
        .should('be.visible')
        .and(($img) => {
            expect($img[0].naturalWidth).to.be.greaterThan(0);
        });
});

Cypress.Commands.add('createNewProduct', (SKU) => {
    // cy.scrollTo(0,0);
    cy.get('a.AknButton.create-product-button').click();
    cy.get('div.AknSquareList-item.product-choice[title="Product"]').click();
    cy.get('input#creation_identifier').type(SKU);

    cy.intercept(/\/configuration\/rest\/family\/.*/).as('getFamilies');

    cy.get('div.select2-container.select2.select-field').click();
    cy.wait('@getFamilies');
    cy.wait(1000);
    cy.get('ul.select2-results li:first').click();

    cy.intercept('POST', /\/enrich\/product\/rest/).as('saveProduct');
    cy.intercept('GET', /\/enrich\/product\/rest\/.*/).as('getProduct');
    cy.get('div.AknButton.AknButtonList-item.AknButton--apply.ok[title="Save"]').click();
    // Wait for loading mask
    cy.get('.AknLoadingMask').should('be.visible');
    cy.wait('@saveProduct');
    cy.wait('@getProduct');
    // Wait for loading mask deletion
    cy.get('.AknLoadingMask').should('not.be.visible');
    cy.get('h1.AknTitleContainer-title').contains(SKU);
});

Cypress.Commands.add('validateCompletionIncrease', (name) => {
    var completeness = 0;
    cy.get('div.completeness-badge button div span span').invoke('text')
        .then(text => {
            cy.log("Completeness : " + text);
            completeness = Number(text.replace('%',''));
        });
    cy.log("Completeness : " + completeness);

    cy.get('span.required-attribute-indicator').click();
    cy.wait(5000);
    cy.get('input.AknTextField:first').clear().type(name);
    
    cy.intercept('GET', /\/enrich\/product\/rest\/.*/).as('getProduct');
    cy.saveProduct();
    cy.wait('@getProduct');

    cy.get('div.completeness-badge button div span span').invoke('text')
        .then(text => {
            cy.log("Completeness : " + text);
            cy.wrap(Number(text.replace('%',''))).should('be.gt', completeness);
        });
});

Cypress.Commands.add('deleteProduct', (SKU) => {
    cy.get('div.AknDefault-mainContent.entity-edit-form.edit-form .AknSecondaryActions-button.dropdown-button').click();
    cy.get('.AknDropdown-menuLink.delete').click();
    cy.intercept('DELETE', /\/enrich\/product\/rest\/.*/).as('deleteProduct');
    cy.get('div.AknButton.AknButtonList-item.AknButton--apply.ok[title="Delete"]').click();
    cy.wait('@deleteProduct');
});

Cypress.Commands.add('exportSingleProductList', () => {
    cy.intercept('GET', /\/datagrid\/export-profile-grid\/load.*/).as('getProductList');
    cy.findByText('Exports').click();
    cy.wait('@getProductList');

    cy.intercept('GET', /\/datagrid\/export-profile-grid.*/).as('filterExportsList');
    cy.get('input[class="AknFilterBox-search"]').click().type("demo");
    cy.wait('@filterExportsList');

    cy.intercept('POST', '/rest/process-tracker').as('getLastExports');
    cy.get('table.AknGrid.grid tbody tr:first td.AknGrid-bodyCell--highlight:first').click();
    cy.wait('@getLastExports');
    
    cy.intercept('POST', /\/job-instance\/rest\/export\/.*\/launch/).as('export');
    cy.findByText('Export now').click()
    cy.get('.AknLoadingMask').should('be.visible');
    cy.wait('@export');
    cy.get('.AknLoadingMask').should('not.be.visible');

    cy.window().document().then(function (doc) {
        var trickCypress;
        doc.addEventListener('click', () => {
            trickCypress = setTimeout(function () { doc.location.reload() }, 2000)
        })
        cy.findByText('Download generated files').click();
        clearTimeout(trickCypress);
    })
});

Cypress.Commands.add('exportMultipleProductList', () => {
    cy.intercept('GET', /\/datagrid\/export-profile-grid\/load.*/).as('getProductList');
    cy.findByText('Exports').click();
    cy.wait('@getProductList');

    cy.intercept('GET', /\/datagrid\/export-profile-grid.*/).as('filterExportsList');
    cy.get('input[class="AknFilterBox-search"]').focus().clear().type("Brands export");
    cy.wait('@filterExportsList');

    cy.intercept('POST', '/rest/process-tracker').as('getLastExports');
    cy.get('body')
    .then(($body) => {
        if ($body.find('table.AknGrid.grid tbody tr:first td.AknGrid-bodyCell--highlight:first').length) {
            cy.get('table.AknGrid.grid tbody tr:first td.AknGrid-bodyCell--highlight:first').click();
            cy.wait('@getLastExports');
            
            cy.intercept('POST', /\/job-instance\/rest\/export\/.*\/launch/).as('export');
            cy.findByText('Export now').click()
            cy.get('.AknLoadingMask').should('be.visible');
            cy.wait('@export');
            cy.get('.AknLoadingMask').should('not.be.visible');
            cy.findByText('Download generated files').click();
            var csv_file = "";
            var href = "";
            cy.get('a:contains("Download generated files")').should('have.attr', 'href')
            .then((filename) => {
                expect(filename).to.match(/.*\.(xlsx|csv)$/)
                href = filename;
                csv_file = filename.match(/(export_.*\.(xlsx|csv))/)[1];
                cy.log(`CSV name **${csv_file}**`)
            }).then(()=>{
                cy.log(`HREF **${href}**`)
                cy.request(href).as('downloadRequest');
            });
        
            cy.get('@downloadRequest').should((response) => {
                assert.isNotNull(response.body, 'File is not empty');
            })
        } else {
            cy.log("No export found with the specified filter, skipping the test");
        }
    })
});

Cypress.Commands.add('disconnect', () => {
    cy.visit("/");
    cy.get('div.AknTitleContainer-userIcon').click();
    cy.get('div.AknDropdown-menuLink.logout').click();
});
