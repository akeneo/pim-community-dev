Feature: Export rules
  In order ease the enrichment of the catalog
  As an administrator
  I need to export all the rules

  Background:
    Given the following locales en_US,fr_FR
    And the following "ecommerce" channel with locales "en_US, fr_FR"
    And the following "mobile" channel with locales "en_US, fr_FR"
    And the family "camcorders"
    And the following categories:
    | code            | parent |
    | 2014_collection |        |
    And I have permission to export rules
    And I import several rules

  @acceptance-back
  Scenario: Export rules
    When I export all the rules
    Then no exception has been thrown
    And the export data contains all rules
