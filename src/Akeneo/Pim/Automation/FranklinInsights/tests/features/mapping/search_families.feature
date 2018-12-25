@acceptance-back
Feature: Search for families

  Scenario: search for all the families
    Given the family "router"
    And the family "camcorders"
    And the family "webcam"
    When I search for all the families
    Then I should have the families router, camcorders and webcam

  Scenario: search families
    Given the family "router"
    And the family "camcorders"
    And the family "webcam"
    When I search a family with the query "cam"
    Then I should have the family camcorders and webcam
