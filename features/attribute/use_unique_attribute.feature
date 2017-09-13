@javascript
Feature: Use an unique attribute
  In order to be able to use unique attribute at the right places
  As a product manager
  I need to be able to use unique attribute only in right places

  Background:
    Given the "footwear" catalog configuration
    And the following attribute:
      | code        | label-en_US      | type             | group | unique |
      | unique_attr | Unique attribute | pim_catalog_text | info  | 1      |
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-6428
  Scenario: Successfully use unique attributes on family edit
    Given I am on the "boots" family page
    When I visit the "Attributes" tab
    Then I should see available attribute Unique attribute
    And I should see available attribute Handmade

  @jira https://akeneo.atlassian.net/browse/PIM-6428
  Scenario: Successfully hide unique attributes on product mass edit
    Given I am on the products grid
    And I create a new product
    And I fill in the following information in the popin:
      | SKU    | a_boot |
      | family | Boots  |
    And I press the "Save" button in the popin
    And I wait to be on the "a_boot" product page
    And I am on the products grid
    And I select all entities
    And I press "Change product information" on the "Bulk Actions" dropdown button
    When I choose the "Edit common attributes" operation
    Then I should not see available attribute Unique attribute
    And I should see available attribute Name
