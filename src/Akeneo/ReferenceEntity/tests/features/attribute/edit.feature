Feature: Lists all attributes related to a reference entity
  In order to see the structure of a reference entity
  As a user
  I want to list all of its attributes

  @acceptance-front
  Scenario: List all attributes of a reference entity
    Given a valid reference entity
    When the user asks for the reference entity "designer"
    And the user edit the attribute "name"
    And the attribute property "maxFileSize" should not be visible
    And the attribute property "allowedExtensions" should not be visible
    Then the user edits the attribute property "Label" with value "Nice Name"
    And the user edits the attribute property "IsRequired" with value "true"
    And the user edits the attribute property "MaxLength" with value "12"
    And the attribute property "isRichTextEditor" should not be visible
    And the user edits the attribute property "IsTextarea" with value "true"
    And the user edits the attribute property "IsRichTextEditor" with value "true"
    And the attribute property "validationRule" should not be visible
    And the user edits the attribute property "IsTextarea" with value "false"
    And the attribute property "isRichTextEditor" should not be visible
    And the attribute property "regularExpression" should not be visible
    And the user edits the attribute property "ValidationRule" with value "email"
    And the attribute property "regularExpression" should not be visible
    And the user edits the attribute property "ValidationRule" with value "url"
    And the attribute property "regularExpression" should not be visible
    And the user edits the attribute property "ValidationRule" with value "regular_expression"
    And the user edits the attribute property "RegularExpression" with value "nice!"

  @acceptance-front
  Scenario: List all attributes of a reference entity
    Given a valid reference entity
    When the user asks for the reference entity "designer"
    And the user edit the attribute "name"
    And the user edit the attribute "portrait"
    And the attribute property "isRichTextEditor" should not be visible
    And the attribute property "isTextarea" should not be visible
    And the attribute property "validationRule" should not be visible
    And the attribute property "regularExpression" should not be visible
    And the attribute property "maxLength" should not be visible
    Then the user edits the attribute property "Label" with value "Nice Name"
    And the user edits the attribute property "IsRequired" with value "true"
    And the user edits the attribute property "MaxFileSize" with value "120.4"
    And the user edits the attribute property "AllowedExtensions" with value "gif"
