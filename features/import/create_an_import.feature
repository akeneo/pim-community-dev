Feature: Create an import
  In order to use my PIM data into my front applications
  As a user
  I need to be able to create import jobs

  @javascript
  Scenario: Successfully create a product import into csv
    Given the "default" catalog configuration
    And I am logged in as "admin"
    And I am on the imports page
    And I create a new "Product import in CSV" import
    When I fill in the following information:
      | Code  | mobile_product_import |
      | Label | Mobile product import |
    And I visit the "Configuration" tab
    And I save the import
    Then I should see "Import profile - Mobile product import"

  Scenario: Fail to create an unknown product import
    Given the "default" catalog configuration
    And I am logged in as "admin"
    When I try to create an unknown import
    Then I should be redirected on the import index page
    And I should see "Failed to create an import with an unknown job definition."
