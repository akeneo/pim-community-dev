Feature: Onboarder Serenity - Suppliers management - validate a supplier

  Scenario: Edit a supplier with a blank label
    Given a supplier with code "supplier1" and label "Supplier1"
    When I update the supplier "supplier1" with a blank label
    Then I should have the following validation errors:
      | path  | message                         |
      | label | This value should not be blank. |

  Scenario: Edit a supplier with a label longer than 200 characters
    Given a supplier with code "supplier1" and label "Supplier1"
    When I update the supplier "supplier1" with a label longer than 200 characters
    Then I should have the following validation errors:
      | path  | message                                                        |
      | label | This value is too long. It should have 200 characters or less. |

  Scenario: Edit a supplier contributor with an invalid email
    Given a supplier with code "supplier1" and label "Supplier1"
    When I update the supplier "supplier1" contributors with "mo@mo"
    Then I should have the following validation errors:
      | path                 | message                                  |
      | contributorEmails[0] | This value is not a valid email address. |

  Scenario: Edit a supplier with an email address longer than 255 characters for a contributor
    Given a supplier with code "supplier1" and label "Supplier1"
    When I update the supplier "supplier1" with an email address longer than 255 for a contributor
    Then I should have the following validation errors:
      | path                 | message                                                        |
      | contributorEmails[0] | This value is too long. It should have 255 characters or less. |
