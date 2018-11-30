Feature: Lists all records of a reference entity
  In order to see the records of a reference entity
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
    Then there should be no result

  @acceptance-back
  Scenario: Search records of a reference entity by their code
    Given the records "starck,dyson,coco"
    When the user filters records by "code" with operator "NOT IN" and value "coco"
    Then the search result should be "starck,dyson"

  @acceptance-back
  Scenario: Search records of a reference entity by their code
    Given the records "starck,dyson,coco"
    When the user filters records by "code" with operator "IN" and value "coco,dyson"
    Then the search result should be "dyson,coco"

  @acceptance-front
  Scenario: List records of a reference entity
    Given the user asks for a list of records
    Then the user should see an unfiltered list of records
    When the user searches for "s"
    Then the user should see a filtered list of records

  @acceptance-front
  Scenario: Search records of a reference entity
    Given the user asks for a list of records
    When the user searches for "s"
    Then the user should see a filtered list of records
    And I switch to another locale in the record grid
    Then the list of records should be empty

  @acceptance-front
  Scenario: Search records of a reference entity
    Given the user asks for a list of records
    When the user searches for "s"
    Then the user should see a filtered list of records

  @acceptance-front
  Scenario: Search records of a reference entity with no results
    Given the user asks for a list of records
    When the user searches for "search"
    Then the list of records should be empty
