Feature: Onboarder Serenity - Suppliers management - create supplier

  Scenario: Create a supplier
    Given there is no supplier
    When I create a supplier with code "supplier1" and label "Supplier1"
    Then I should have a supplier with code "supplier1" and label "Supplier1"

  Scenario: Supplier code is unique
    Given a supplier "supplier1"
    When I create a supplier with code "supplier1"
    Then an error is thrown because this supplier already exists
