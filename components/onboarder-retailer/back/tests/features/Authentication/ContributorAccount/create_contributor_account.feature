Feature: Onboarder Serenity - Authentication - create contributor account

  @onboarder-serenity-contributor-authentication-enabled
  Scenario: Add contributor to supplier and create a contributor account
    Given a supplier with code "supplier1" and label "Supplier1"
    When I update the supplier "supplier1" contributors with "contrib1@example.com;contrib2@example.com"
    Then I should have "contrib1@example.com;contrib2@example.com" contributor accounts
