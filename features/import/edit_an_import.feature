Feature: Edit an import
  In order to manage existing import jobs
  As a user
  I need to be able to edit an import job

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "admin"

  Scenario: Successfully edit an import job
    Given I am on the "footwear_product_import" import job edit page
    Then I should see the Code field
    And the field Code should be disabled
    When I fill in the following information:
      | Label | My import |
    And I press the "Save" button
    Then I should see "My import"

  @javascript
  Scenario: Successfully update import job configuration
    Given I am on the "footwear_product_import" import job edit page
    Then I should see the File, Allow file upload, Delimiter, Enclosure, Escape, Enable the product?, Categories column, Family column, Groups column and Real time versioning? fields
    When I fill in the following information:
      | File              | /file.csv |
      | Delimiter         | \|        |
      | Enclosure         | '         |
      | Escape            | \\        |
      | Categories column | cat       |
      | Family column     | fam       |
      | Groups column     | grp       |
    And I uncheck the "Allow file upload" switch
    And I uncheck the "Enable the product?" switch
    And I uncheck the "Real time versioning?" switch
    And I press the "Save" button
    Then I should see "File /tmp/file.csv"
    And I should see "Allow file upload No"
    And I should see "Delimiter \"
    And I should see "Enclosure '"
    And I should see "Escape \\"
    And I should see "Real time versioning? No"
    And I should see "Enable the product? No"
    And I should see "Categories column cat"
    And I should see "Family column fam"
    And I should see "Groups column grp"

  @javascript
  Scenario: Successfully display a message when there are unsaved changes
    Given I am on the "footwear_product_import" import job edit page
    When I fill in the following information:
      | Label | My import |
    Then I should see "There are unsaved changes."
    When I click on the Akeneo logo
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                           |
      | content | You will lose changes to the import profile if you leave this page. |
