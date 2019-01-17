Feature: Validate family
  In order to create a valid family
  As a manager
  I need to validate the information provided

  @acceptance-back
  Scenario: Fail to create an family with an empty code
    When I create an family with a code ""
    Then the family should be invalid with message "This value should not be blank."

  @acceptance-back
  Scenario: Fail to create an family with an invalid code
    When I create an family with a code "A=(B"
    Then the family should be invalid with message "Family code may contain only letters, numbers and underscores"

  @acceptance-back
  Scenario: Fail to create an family with a code longer than 100 characters
    When I create a family with a code > 100 characters
    Then the family should be invalid with message "This value is too long. It should have 100 characters or less."
