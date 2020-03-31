Feature: Import calculate action rules
    In order ease the enrichment of the catalog
    As an administrator
    I need to be able to create rules

Background:
    Given the family "camcorders"
    And the product "75024" of the family "camcorders"
    And I have permission to import rules

    @acceptance-back @toto
    Scenario: Import a valid calculate rule
        When I import a valid calculate rule
        Then no exception has been thrown
        And the rule list contains the imported calculate rule
