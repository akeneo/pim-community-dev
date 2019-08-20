@javascript
Feature: Import profiles
  In order to easily import profiles
  As a product manager
  I need to be able to see the result of an import and to download logs and files

  Background:
    Given a "footwear" catalog configuration
    And the following CSV file to import:
      """
      sku;sole_color;groups
      my-jacket;red;jacket
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    Then I am logged in as "Peter"

  Scenario: Go to the job execution page for an "import" and then check buttons status on the header and "Show profile" button redirection
    Given I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "COMPLETED"
    And I should see the text "Execution details - CSV footwear product import [csv_footwear_product_import]"
    And I should see "Download invalid data" on the "Download generated files" dropdown button
    And I should see "Download read files" on the "Download generated files" dropdown button
    And I should see the secondary action "Show profile"
    When I press the secondary action "Show profile"
    Then I should be redirected on the import page of "csv_footwear_product_import"

  Scenario: Go to the import job execution page without rights to download generated files
    Given I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resources Download imported files
    And I save the role
    And I should not see the text "There are unsaved changes."
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "COMPLETED"
    And I should see the text "Execution details - CSV footwear product import [csv_footwear_product_import]"
    And I should see the secondary action "Download log"
    And I should see the secondary action "Show profile"
    When I press the secondary action "Show profile"
    Then I should be redirected on the import page of "csv_footwear_product_import"
