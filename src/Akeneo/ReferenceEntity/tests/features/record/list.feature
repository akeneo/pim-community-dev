Feature: Lists all records of an reference entity
  In order to see the records of an reference entity
  As a user
  I want to list all of its records

  @acceptance-back
  Scenario: Search records of a reference entity
    Given the records "starck,dyson,coco"
    When the user search for "s"
    Then the search result should be "starck,dyson"

  @acceptance-back
  Scenario: List records of a reference entity
    Given the records "starck,dyson,coco"
    When the user list the records
    Then the search result should be "starck,dyson,coco"

  @acceptance-back
  Scenario: Search records of a reference entity with no results
    Given the records "starck,dyson,coco"
    When the user search for "search"
    Then the search result should be ""

  @acceptance-front
  Scenario: List records of a reference entity
    Given the user ask for a list of records
    Then the user should see an unfiltered list of records
    When the user search for "s"
    Then the user should see a filtered list of records

  @acceptance-front
  Scenario: Search records of a reference entity
    Given the user ask for a list of records
    When the user search for "s"
    Then the user should see a filtered list of records

  @acceptance-front
  Scenario: Search records of a reference entity with no results
    Given the user ask for a list of records
    When the user search for "search"
    Then the list of records should be empty
