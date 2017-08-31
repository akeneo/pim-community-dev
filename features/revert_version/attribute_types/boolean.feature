@javascript
Feature: Revert product attributes to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert product attributes to a previous version

  Background:
    Given a "clothing" catalog configuration
    And the following products:
      | sku   | family  | handmade |
      | jeans | jackets | 1        |
      | short | jackets |          |
    And I am logged in as "Julia"

  Scenario: Successfully revert a boolean attribute
    Given I am on the "jeans" product page
    And I uncheck the "Handmade" switch
    When I save the product
    Then I should not see the text "There are unsaved changes."
    When the history of the product "jeans" has been built
    And I visit the "History" column tab
    Then I should see 2 versions in the history
    When I revert the product version number 1
    Then the product "jeans" should have the following values:
      | handmade | 1 |
