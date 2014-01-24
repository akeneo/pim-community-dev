@javascript
Feature: Validate the product against the attribute constraints
    In order to have a error-free set of products
    As Julia
    I need to be unable to provide invalid informations to the products

    Scenario: Fail to set the same identifier value on two different products
        Given a "footwear" catalog configuration
        And the following product:
          | sku    |
          | boots  |
          | hiking |
        And I am logged in as "Julia"
        When I am on the "boots" product page
        And I change the SKU to "hiking"
        And I save the product
        Then I should see a validation tooltip "This value is already set on another product."
