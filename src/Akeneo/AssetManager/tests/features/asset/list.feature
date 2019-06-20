Feature: Lists all assets of an asset family
  In order to see the assets of an asset family
  As a user
  I want to list all of its assets

  @acceptance-back
  Scenario: Search assets of an asset family
    Given a list of assets
    When the user search for "s"
    Then the search result should be "starck,dyson"

  @acceptance-back
  Scenario: List assets of an asset family
    Given a list of assets
    When the user list the assets
    Then the search result should be "starck,dyson,coco"

  @acceptance-back
  Scenario: Search assets of an asset family with no results
    Given a list of assets
    When the user search for "search"
    Then there should be no result on a total of 3 assets

  @acceptance-back
  Scenario: Search assets of an asset family by their code
    Given a list of assets
    When the user filters assets by "code" with operator "NOT IN" and value "coco"
    Then the search result should be "starck,dyson"

  @acceptance-back
  Scenario: Search assets of an asset family by their code
    Given a list of assets
    When the user filters assets by "code" with operator "IN" and value "dyson"
    Then the search result should be "dyson"

  @acceptance-front
  Scenario: List assets of an asset family
    Given the user asks for a list of assets
    Then the user should see an unfiltered list of assets
    When the user searches for "s"
    Then the user should see a filtered list of assets

  @acceptance-front
  Scenario: Search assets of an asset family
    Given the user asks for a list of assets
    When the user searches for "s"
    Then the user should see a filtered list of assets
    And I switch to another locale in the asset grid
    Then the list of assets should be empty

  @acceptance-front
  Scenario: Search assets of an asset family
    Given the user asks for a list of assets
    When the user searches for "s"
    Then the user should see a filtered list of assets

  @acceptance-front
  Scenario: Search assets of an asset family with no results
    Given the user asks for a list of assets
    When the user searches for "search"
    Then the list of assets should be empty

  @acceptance-front
  Scenario: Search assets of an asset family with red option
    Given the user asks for a list of assets
    When the user searches for assets with red color
    Then the user should see a filtered list of red assets

  @acceptance-front
  Scenario: Search assets of an asset family with city link
    Given the user asks for a list of assets
    When the user searches for assets with linked to paris
    Then the user should see a filtered list of assets linked to paris

  @acceptance-front
  Scenario: Filter only the complete assets of an asset family
    Given the user asks for a list of assets
    When the user filters on the complete assets
    Then the user should see a list of complete assets

  @acceptance-front
  Scenario: Filter only the uncomplete assets of an asset family
    Given the user asks for a list of assets
    When the user filters on the uncomplete assets
    Then the user should see a list of uncomplete assets

  # @acceptance-front TODO fix random
  Scenario: Display completeness of assets on the grid
    Given the user asks for a list of assets having different completenesses
    Then the user should see that "starck" is complete at 50%
    And the user should see that "dyson" is complete at 0%
    And the user should see that "coco" is complete at 100%
