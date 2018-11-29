@javascript
Feature: Remove an attribute from a product
  In order to reduce undesired amount of attributes on a product
  As a product manager
  I need to be able to remove an attribute from a product

  Background:
    Given a "footwear" catalog configuration
    And the following product:
      | sku  | family  |
      | nike | sandals |
    And I am logged in as "Sandra"

  Scenario: Fail to remove an attribute belonging to the family of the product
    Given I am on the "nike" product page
    Then I should not see a remove link next to the "Manufacturer" field

  Scenario: Successfully remove a scopable attribute from a product
    Given the following attribute:
      | code            | label-en_US     | scopable | group | type             |
      | scopable_length | Scopable length | 1        | sizes | pim_catalog_text |
    And the "nike" product has the "scopable_length" attribute
    And I am on the "nike" product page
    When I visit the "Sizes" group
    And I remove the "Scopable length" attribute
    Then I confirm the deletion
    And I press the "Save" button
    And attribute in group "Sizes" should be Size
