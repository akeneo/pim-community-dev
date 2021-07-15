Feature: Validate attribute properties
  In order to build a consistent catalog
  As a catalog manager
  I need to be able to see validation errors for attributes

  @acceptance-back
  Scenario Outline: Fail to create an attribute with a blacklisted code, regardless of the case
    When an attribute is created with the code "<code>"
    Then I should see a validation error "This code is not available"
    Examples:
      | code             |
      | id               |
      | Id               |
      | categories       |
      | CaTeGORies       |
      | associationTypes |
      | assOciationtypes |
      | CATEGORYid       |
      | famILY           |
      | GROUPS           |
      | Entity_Type      |
