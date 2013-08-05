Feature: Create an import
  In order to use my PIM data into my front applications
  As a user
  I need to be able to create import jobs

  @javascript
  Scenario: Successfully display the product import into csv configuration form
    Given I am logged in as "admin"
    And I am on the imports page
    And I create a new "Product import in CSV" import
    And I visit the "Import" tab
    Then I should see "Reader - Dummy reader"
    And I should see "Processor - Dummy processor"
    And I should see "Writer - Dummy writer"

  @javascript
  Scenario: Successfully create a product import into csv
    Given I am logged in as "admin"
    And I am on the imports page
    And I create a new "Product import in CSV" import
    When I fill in the following information:
      | Code  | mobile_product_import |
      | Label | Mobile product import |
    And I visit the "Import" tab
    And I save the import
    Then I should be on the "mobile_product_import" import job page
    And I should see "The import has been successfully created."

  Scenario: Fail to create an unknown product import
    Given I am logged in as "admin"
    And I try to create an unknown import
    Then I should be redirected on the import index page
    And I should see "Failed to create an import with an unknown job definition."
