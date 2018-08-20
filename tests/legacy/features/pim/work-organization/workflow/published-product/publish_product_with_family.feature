Feature: Publish product with a family
  In order to publish products
  As an internal process or any user
  I need to be able to publish a product belonging to a family

  Scenario: Successfully publish a product belonging to a family
    Given a "default" catalog configuration
    And the following family:
      | code  | requirements-ecommerce | requirements-mobile |
      | shirt | sku                    | sku                 |
    And the following products:
      | sku     | family |
      | tshirt1 | shirt  |
    And I publish the product "tshirt1"
    Then I should get the following published product:
      | product | result              |
      | tshirt1 | {"family": "shirt"} |
