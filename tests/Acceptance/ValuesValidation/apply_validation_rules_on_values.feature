Feature: Apply validation rules on values
  In order to guarantee the quality of my data
  As a regular user
  I need to be able to validate values using attribute validation rules and be properly informed by a message

  Background:
    Given an identifier attribute has been created

  Scenario: Forbid incorrect email values
    Given a text attribute "email_address" with a validation rule "email" has been created
    When I create a product containing the following values:
      | email_address | not a valid email address |
    Then the email_address value should be invalid with the message "This value is not a valid email address."

  Scenario: Forbid incorrect URL values
    Given a text attribute "link" with a validation rule "url" has been created
    When I create a product containing the following values:
      | link | https:/www.akeneo.com |
    Then the link value should be invalid with the message "This value is not a valid URL."

  Scenario: Forbid values not matching a regular expression
    Given a text attribute "custom_code" with a validation rule "regexp" and a pattern "/^[a-z]*$/" has been created
    When I create a product containing the following values:
      | custom_code | 42 |
    Then the custom_code value should be invalid with the message "This value is not valid."
