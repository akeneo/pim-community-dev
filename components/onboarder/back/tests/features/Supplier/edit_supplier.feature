Feature: Onboarder Serenity - Suppliers management - edit a supplier

  Scenario: Edit a supplier label
    Given a supplier with code "supplier1" and label "Supplier1"
    When I update the supplier "supplier1" label with "The Supplier 1"
    Then I should have a supplier with code "supplier1" and label "The Supplier 1"

  Scenario: Edit a supplier - remove its contributors
    Given a supplier with code "supplier1" and label "Supplier1" and "2" contributors
    When I update the supplier "supplier1" contributors with ""
    Then I should have a supplier with code "supplier1" and contributors ""
