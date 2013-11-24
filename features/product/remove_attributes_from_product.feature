Feature: Remove an attribute from a product
  In order to reduce undesired amount of attributes on a product
  As a user
  I need to be able to remove an attribute from a product

  Background:
    Given a "footwear" catalog configuration
    And the following product:
      | sku  | family  |
      | nike | sandals |
    And I am logged in as "admin"

  Scenario: Fail to remove an attribute belonging to the family of the product
    Given I am on the "nike" product page
    Then I should not see a remove link next to the "Manufacturer" field

  @javascript
  Scenario: Successfully remove an attribute from a product
    Given the following product values:
      | product | attribute  | value |
      | nike    | lace_color | black |
    And I am on the "nike" product page
    And I visit the "Colors" group
    When I remove the "Lace color" attribute
    Then I should see flash message "Attribute successfully removed from the product"
    And attribute in group "Colors" should be Color

  @javascript
  Scenario: Successfully remove a scopable attribute from a product
    Given the following attribute:
      | label  | scopable | group |
      | Length | yes      | sizes |
    And the following product values:
      | product | attribute | scope  | value |
      | nike    | length  | tablet |       |
      | nike    | length  | mobile |       |
    And I am on the "nike" product page
    When I visit the "Sizes" group
    And I remove the "Length" attribute
    Then I should see flash message "Attribute successfully removed from the product"
    And attribute in group "Sizes" should be Size
