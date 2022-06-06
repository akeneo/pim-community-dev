Feature: Onboarder Serenity - Suppliers management - get a supplier

  Scenario: Get a supplier
    Given a supplier with code "supplier1" and label "Supplier1" and "2" contributors
    Given a supplier with code "supplier2" and label "Supplier2"
    Then I should have a supplier with code "supplier1" and contributors "email1@example.com;email2@example.com"
    Then I should have a supplier with code "supplier2" and contributors ""
