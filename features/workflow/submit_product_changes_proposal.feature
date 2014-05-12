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
    And role "Administrator" has the right to edit the attribute group "info"
    And I am logged in as "admin"
    When I edit the "my-sandals" product
    And I change the Name to "Basket"
    And I save the product
    Then the product Name should be "Sandals"
    And there should be 1 product changes proposal

  Scenario: Succesfully view a product changes proposal
