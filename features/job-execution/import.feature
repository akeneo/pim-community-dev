@javascript
Feature: Import profiles
  In order to easily import profiles
  As a product manager
  I need to be able to see the result of an import and to download logs and files

  Background:
    Given a "footwear" catalog configuration
    And the following reference data:
      | type  | code | label |
      | color | red  | Red   |
      | color | blue | Blue  |
    And the following variant groups:
      | code   | label-en_US | axis       | type    |
      | jacket | Jacket      | sole_color | VARIANT |
    And the following products:
      | sku       | sole_color | groups |
      | my-jacket | red        | jacket |
    Then I am logged in as "Julia"

  Scenario: Go to the job execution page for an "import" (directly) and then check buttons status on the header and "Show profile" button redirection
    Given the following CSV file to import:
      """
      sku;sole_color;groups
      my-jacket;red;jacket
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "COMPLETED"
    And I should see the text "Execution details - CSV footwear product import [csv_footwear_product_import]"
    And I should see the text "Download log"
    And I should see the text "Download read files"
    And I should not see the text "Download generated file"
    And I should not see the text "Download generated archive"
    And I should see the text "Show profile"
    When I press the "Show profile" button
    Then I should be redirected on the import page of "csv_footwear_product_import"

  Scenario: Go to the job execution page for an "import" (by clicking on notifications) and check buttons status on the header and "Show profile" button redirection
    Given the following CSV file to import:
      """
      sku;sole_color;groups
      my-jacket;red;jacket
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    When I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                      |
      | success | Import CSV footwear product import finished  |
    When I go on the last executed job resume of "csv_footwear_product_import"
    Then I should see the text "COMPLETED"
    And I should see the text "Execution details - CSV footwear product import [csv_footwear_product_import]"
    And I should see the text "Download log"
    And I should see the text "Download read files"
    And I should not see the text "Download generated file"
    And I should not see the text "Download generated archive"
    And I should see the text "Show profile"
    When I press the "Show profile" button
    Then I should be redirected on the import page of "csv_footwear_product_import"