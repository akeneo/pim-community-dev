@javascript
Feature: Submit a product changes proposal
  In order to contribute to a product edition
  As a contributor
  I need to be able to submit a product changes proposal

  # TODO Change admin when contributor and owner roles have been introduced
  Scenario: Succesfully propose changes to a product
    Given a "footwear" catalog configuration
    And the following product:
    | sku        | family  | name-en_US |
    | my-sandals | sandals | Sandals    |
    And role "Administrator" has the permission to edit the attribute group "info"
    And I am logged in as "admin"
    When I edit the "my-sandals" product
    And I change the Name to "Basket"
    And I save the product
    Then the product Name should be "Sandals"
    When I visit the "Proposals" tab
    Then the grid should contain 1 element

  Scenario: Fail to propose an empty change set to a product
    Given a "footwear" catalog configuration
    And the following product:
    | sku        | family  | name-en_US |
    | my-sandals | sandals | Sandals    |
    And role "Administrator" has the permission to edit the attribute group "info"
    And I am logged in as "admin"
    When I edit the "my-sandals" product
    And I save the product
    When I visit the "Proposals" tab
    Then the grid should contain 0 element
