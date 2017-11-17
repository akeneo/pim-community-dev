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
    And I visit the "Global settings" tab
    Then I should see the File, Allow file upload, Delimiter, Enclosure, Enable the product, Categories column, Family column, Groups column, Real time history update, Decimal separator, Date format fields
    When I fill in the following information:
      | File              | /tmp/file.csv |
      | Delimiter         | \|            |
      | Enclosure         | '             |
      | Categories column | cat           |
      | Family column     | fam           |
      | Groups column     | grp           |
      | Decimal separator | .             |
      | Date format       | yyyy-mm-dd    |
    And I visit the "Global settings" tab
    And I uncheck the "Allow file upload" switch
    And I uncheck the "Enable the product" switch
    And I uncheck the "Real time history update" switch
    And I press the "Save" button
    And I should not see the text "There are unsaved changes."
    And I press the "Edit" button
    Then I should see the text "File path"
    And the "File path" field should contain "/tmp/file.csv"
    And I should see the text "Delimiter"
    And the "Delimiter" field should contain "|"
    And I should see the text "Enclosure"
    And the "Enclosure" field should contain "'"
    And I should see the text "Real time history"
    And the "Real time history" field should contain ""
    And I should see the text "Enable the product"
    And the "Enable the product" field should contain ""
    And I should see the text "Categories column"
    And the "Categories column" field should contain "cat"
    And I should see the text "Family column"
    And the "Family column" field should contain "fam"
    And I should see the text "Groups column"
    And the "Groups column" field should contain "grp"
    And I should see the text "Decimal separator"
    And I should see the text "Date format"
    And I should see the text "Allow file upload"
    And the "Allow file upload" field should contain ""

  Scenario: Successfully update XLSX import job configuration
    Given I am on the "xlsx_footwear_product_import" import job edit page
    Then I should see the File, Allow file upload, Enable the product, Categories column, Family column, Groups column, Real time history update, Decimal separator, Date format fields
    When I fill in the following information:
      | File              | /tmp/file.csv |
      | Categories column | cat           |
      | Family column     | fam           |
      | Groups column     | grp           |
      | Decimal separator | dot (.)       |
      | Date format       | dd/mm/yyyy    |
    And I uncheck the "Allow file upload" switch
    And I uncheck the "Enable the product" switch
    And I uncheck the "Real time history update" switch
    And I press the "Save" button
    And I should not see the text "There are unsaved changes."
    Then I should see the text "File path"
    And the "File path" field should contain "/tmp/file.csv"
    And I should see the text "Real time history"
    And the "Real time history" field should contain ""
    And I should see the text "Enable the product"
    And the "Enable the product" field should contain ""
    And I should see the text "Categories column"
    And the "Categories column" field should contain "cat"
    And I should see the text "Family column"
    And the "Family column" field should contain "fam"
    And I should see the text "Groups column"
    And the "Groups column" field should contain "grp"
    And I should see the text "Decimal separator"
    And the field Decimal separator should contain "dot (.)"
    And I should see the text "Date format"
    And the field Date format should contain "dd/mm/yyyy"
    And I should see the text "Allow file upload"
    And the "Allow file upload" field should contain ""

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
