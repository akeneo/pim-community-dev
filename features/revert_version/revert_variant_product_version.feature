@javascript
Feature: Revert a variant product to a previous version
  In order to manage versioning for variant products
  As a product manager
  I need to be able to revert a variant product to a previous version

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully revert a variant product
    Given I am on the "1111111119" product page
    And I change the "EAN" to "123456789013142"
    And I change the "Weight" to "400"
    And I save the product
    Then I should not see the text "There are unsaved changes"
    When I visit the "History" column tab
    Then I should see 2 versions in the history
    And I should see history:
      | version | author      | property | value           |
      | 2       | Julia Stark | EAN      | 123456789013142 |
      | 2       | Julia Stark | Weight   | 400             |
    When I revert the product version number 1
    And I visit the "History" column tab
    Then I should see 3 versions in the history
    And I should see history:
      | version | author      | property | value           |
      | 3       | Julia Stark | EAN      | 1234567890131   |
      | 3       | Julia Stark | Weight   | 800             |
      | 2       | Julia Stark | EAN      | 123456789013142 |
      | 2       | Julia Stark | Weight   | 400             |
