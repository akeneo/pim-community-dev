Feature: Onboarder Serenity - Suppliers management - delete supplier

  Scenario: delete a supplier
    Given a supplier "supplier1"
    And a supplier "supplier2"
    And a supplier "supplier3"
    When I delete the supplier "supplier2"
    Then I should have the following suppliers:
      | code      | label     |
      | supplier1 | supplier1 |
      | supplier3 | supplier3 |
