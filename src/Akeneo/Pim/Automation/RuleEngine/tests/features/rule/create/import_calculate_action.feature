Feature: Import calculate action rules
    In order ease the enrichment of the catalog
    As an administrator
    I need to be able to create rules

Background:
    Given the following locales en_US,fr_FR
    And the following ecommerce channel with locales en_US,fr_FR
    And the following mobile channel with locales en_US
    And the family "camcorders"
    And I have permission to import rules

    @acceptance-back
    Scenario: Import a valid calculate rule
        When I import a valid calculate rule
        Then no exception has been thrown
        And the rule list contains the imported calculate rule

    @acceptance-back
    Scenario: Import a calculate rule with non numeric attribute types
        When I import a calculate rule with invalid attribute types
        And an exception with message "actions[0].destination.field: Invalid attribute type for \"name\", expected a number, measurement or price collection attribute" has been thrown
        And an exception with message "actions[0].source.field: Invalid attribute type for \"description\", expected a number, measurement or price collection attribute" has been thrown
        And an exception with message "actions[0].operation_list[0].field: Invalid attribute type for \"color\", expected a number, measurement or price collection attribute" has been thrown
