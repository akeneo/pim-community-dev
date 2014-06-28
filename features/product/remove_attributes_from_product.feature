Feature: Remove an attribute from a product
  In order to reduce undesired amount of attributes on a product
  As a product manager
  I need to be able to remove an attribute from a product

  Background:
    Given a "footwear" catalog configuration
    And the following product:
      | sku  | family  |
      | nike | sandals |
    And I am logged in as "Julia"

  Scenario: Fail to remove an attribute belonging to the family of the product
    Given I am on the "nike" product page
    Then I should not see a remove link next to the "Manufacturer" field

  @javascript
  Scenario: Successfully remove an attribute from a product
    Given the following product values:
      | product | attribute  | value       |
      | nike    | lace_color | laces_black |
    And I am on the "nike" product page
    And I visit the "Colors" group
    When I remove the "Lace color" attribute
    Then I should see flash message "Attribute successfully removed from the product"
    And attribute in group "Colors" should be Color

  @javascript
  Scenario: Successfully remove a scopable attribute from a product
    Given the following attribute:
      | code            | label           | scopable | group |
      | scopable_length | Scopable length | yes      | sizes |
    And the "nike" product has the "scopable_length" attribute
    And I am on the "nike" product page
    When I visit the "Sizes" group
    And I remove the "Scopable length" attribute
    Then I should see flash message "Attribute successfully removed from the product"
    And attribute in group "Sizes" should be Size
