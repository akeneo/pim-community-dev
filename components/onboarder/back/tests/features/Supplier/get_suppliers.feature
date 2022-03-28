Feature: Onboarder Serenity - Suppliers management - get a supplier

  Scenario: Get a supplier
    Given a supplier with code "supplier1" and label "Supplier1" and "2" contributors
    Given a supplier with code "supplier2" and label "Supplier2"
    When I retrieve the supplier "supplier1"
    Then I should have the following supplier:
      | code      | label     | contributors                        |
      | supplier1 | Supplier1 | email1@akeneo.com;email2@akeneo.com |
    When I retrieve the supplier "supplier2"
    Then I should have the following supplier:
      | code      | label     | contributors |
      | supplier2 | Supplier2 |              |
