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
    Then there should be no result on a total of 3 records

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

  @acceptance-front
  Scenario: Search records of a reference entity with red option
    Given the user asks for a list of records
    When the user searches for records with red color
    Then the user should see a filtered list of red records

  @acceptance-front
  Scenario: Search records of a reference entity with city link
    Given the user asks for a list of records
    When the user searches for records with linked to paris
    Then the user should see a filtered list of records linked to paris

  @acceptance-front
  Scenario: Filter only the complete records of a reference entity
    Given the user asks for a list of records
    When the user filters on the complete records
    Then the user should see a list of complete records

  @acceptance-front
  Scenario: Filter only the uncomplete records of a reference entity
    Given the user asks for a list of records
    When the user filters on the uncomplete records
    Then the user should see a list of uncomplete records

  @acceptance-front
  Scenario: Display completeness of records on the grid
    Given the user asks for a list of records having different completenesses
    Then the user should see that "starck" is complete at 50%
    And the user should see that "dyson" is complete at 0%
    And the user should see that "coco" is complete at 100%
