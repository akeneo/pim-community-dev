Feature: Create an export
  In order to use my PIM data into my front applications
  As an user
  I need to be able to create export jobs

  Scenario: Successfully display the product export into csv configuration form
    Given I am logged in as "admin"
    And I am on the exports index page
    And I create a new "Product export in CSV" export
    Then I should see "Reader - Scoped products"
    And I should see "Processor - CSV Serializer"
    And I should see "Writer - File"

  @javascript
  Scenario: Successfully create a product export into csv
    Given I am logged in as "admin"
    And I am on the exports index page
    And I create a new "Product export in CSV" export
    When I fill in the following informations:
      | Code       | mobile_product_export |
      | Label      | Mobile product export |
    And I save the export
    Then I should be redirected on the export index page
    And I should see "The export has been successfully created."
    And the grid should contain 1 element

  @javascript
  Scenario: Fail to create an unknown product export
    Given I am logged in as "admin"
    And I try to create an unknown export
    Then I should be redirected on the export index page
    And I should see "Fail to create an export with an unknown job."

  @javascript
  Scenario: Successfully display the Draft status for not configured jobs
    Given the following export job:
      | code           | label   | connector | alias          |
      | export_product | product | Akeneo    | product_export |
    And I am logged in as "admin"
    When I am on the exports index page
    Then the status of the export "export_product" should be "Draft"
