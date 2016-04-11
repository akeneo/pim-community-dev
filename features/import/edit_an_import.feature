@javascript
Feature: Edit an import
  In order to manage existing import jobs
  As an administrator
  I need to be able to edit an import job

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully edit an import job
    Given I am on the "csv_footwear_product_import" import job edit page
    Then I should see the Code field
    And the field Code should be disabled
    When I fill in the following information:
      | Label | My import |
    And I press the "Save" button
    Then I should see the text "My import"

  Scenario: Successfully update import job configuration
    Given I am on the "csv_footwear_product_import" import job edit page
    Then I should see the File, Allow file upload, Delimiter, Enclosure, Escape, Enable the product, Categories column, Family column, Groups column, Real time history update, Decimal separator and Date format fields
    When I fill in the following information:
      | File               | file.csv   |
      | Delimiter          | \|         |
      | Enclosure          | '          |
      | Escape             | \\         |
      | Categories column  | cat        |
      | Family column      | fam        |
      | Groups column      | grp        |
      | Decimal separator  | .          |
      | Date format        | yyyy-MM-dd |
    And I uncheck the "Allow file upload" switch
    And I uncheck the "Enable the product" switch
    And I uncheck the "Real time history update" switch
    And I press the "Save" button
    Then I should see the text "File file.csv"
    And I should see the text "Allow file upload No"
    And I should see the text "Delimiter |"
    And I should see the text "Enclosure '"
    And I should see the text "Escape \\"
    And I should see the text "Real time history update No"
    And I should see the text "Enable the product No"
    And I should see the text "Categories column cat"
    And I should see the text "Family column fam"
    And I should see the text "Groups column grp"
    And I should see the text "Decimal separator dot (.)"
    And I should see the text "Date format yyyy-mm-dd"

  Scenario: Successfully display a dialog when we quit a page with unsaved changes
    Given I am on the "csv_footwear_product_import" import job edit page
    When I fill in the following information:
      | Label | My import |
    When I click on the Akeneo logo
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                           |
      | content | You will lose changes to the import profile if you leave this page. |

  Scenario: Successfully display a message when there are unsaved changes
    Given I am on the "csv_footwear_product_import" import job edit page
    When I fill in the following information:
      | Label | My import |
    Then I should see the text "There are unsaved changes."
