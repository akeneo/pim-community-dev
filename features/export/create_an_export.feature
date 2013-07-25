Feature: Create an export
  In order to use my PIM data into my front applications
  As an user
  I need to be able to create export jobs

  Scenario: Successfully create a product export into csv
    Given I am logged in as "admin"
    And I am on the exports index page
    And I create a new "Product export in CSV" export
    Then I should see "Reader - Scoped products"
    And I should see "Processor - CSV Serializer"
    And I should see "Writer - File"

