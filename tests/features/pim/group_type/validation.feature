Feature: Validate group type
  In order to create a valid group type
  As a manager
  I need to validate the information provided

  @acceptance-back
  Scenario: Fail to create an group type with an empty code
    When I create an group type with a code ""
    Then the group type should be invalid with message "This value should not be blank."

  @acceptance-back
  Scenario: Fail to create an group type with an invalid code
    When I create an group type with a code "A=(B"
    Then the group type should be invalid with message "Group type code may contain only letters, numbers and underscores."

  @acceptance-back
  Scenario: Fail to create an group type with a code longer than 100 characters
    When I create a group type with a code > 100 characters
    Then the group type should be invalid with message "This value is too long. It should have 100 characters or less."
