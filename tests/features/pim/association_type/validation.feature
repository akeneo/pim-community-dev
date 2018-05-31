Feature: Validate association type
  In order to create a valid association type
  As a manager
  I need to validate the information provided

  @acceptance-back
  Scenario: Fail to create an association type with an empty code
    When I create an association type with a code ""
    Then the association type should be invalid with message "This value should not be blank."

  @acceptance-back
  Scenario: Fail to create an association type with an invalid code
    When I create an association type with a code "A=(B"
    Then the association type should be invalid with message "Association type code may contain only letters, numbers and underscores"

  @acceptance-back
  Scenario: Fail to create an association type with code longer than 100 characters
    When I create an association type with a code > 100 characters
    Then the association type should be invalid with message "This value is too long. It should have 100 characters or less."
