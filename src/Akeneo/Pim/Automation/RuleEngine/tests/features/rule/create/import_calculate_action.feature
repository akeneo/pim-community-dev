Feature: Import calculate action rules
    In order ease the enrichment of the catalog
    As an administrator
    I need to be able to create rules

Background:
    Given the following locales en_US,fr_FR
    And the following ecommerce channel with locales en_US,fr_FR
    And the family "camcorders"
    And I have permission to import rules

    @acceptance-back
    Scenario: Import a valid calculate rule
        When I import a valid calculate rule
        Then no exception has been thrown
        And the rule list contains the imported calculate rule

    @acceptance-back
    Scenario: Import a valid calculate rule with round_precision parameter
        When I import a valid calculate rule with round_precision parameter
        Then no exception has been thrown
        And the rule list contains the imported calculate_with_round rule

    @acceptance-back
    Scenario: Import a calculate rule with invalid channels
        When I import a calculate rule with invalid channels
        Then an exception with message "actions[0].destination.scope: The \"print\" channel does not exist" has been thrown
        And an exception with message "actions[0].source.field: The \"item_weight\" attribute code is scopable and no channel is provided" has been thrown
        And an exception with message "actions[0].operation_list[0].field: The \"in_stock\" attribute code is not scopable and a channel is provided" has been thrown

    @acceptance-back
    Scenario: Import a calculate rule with invalid locales
        When I import a calculate rule with invalid locales
        Then an exception with message "actions[0].destination.locale: The \"es_ES\" locale does not exist or is not activate" has been thrown
        And an exception with message "actions[0].source.field: The \"item_weight\" attribute code is localizable and no locale is provided" has been thrown
        And an exception with message "actions[0].operation_list[0].field: The \"in_stock\" attribute code is not localizable and a locale is provided" has been thrown

    @acceptance-back
    Scenario: Import a calculate rule with non numeric attribute types
        When I import a calculate rule with invalid attribute types
        Then an exception with message "actions[0].destination.field: Invalid attribute type for \"name\", expected a number, measurement or price collection attribute" has been thrown
        And an exception with message "actions[0].source.field: Invalid attribute type for \"description\", expected a number, measurement or price collection attribute" has been thrown
        And an exception with message "actions[0].operation_list[0].field: Invalid attribute type for \"color\", expected a number, measurement or price collection attribute" has been thrown

    @acceptance-back
    Scenario: Import a calculate rule with invalid currencies
        When I import a calculate rule with invalid currencies
        Then an exception with message "actions[0].destination.currency: Expected a valid currency, but got none" has been thrown
        And an exception with message "actions[0].source.currency: Expected a valid currency, the \"USD\" currency does not exist or is not activated" has been thrown

    @acceptance-back
    Scenario: Import a calculate rule with invalid measurement unit
        When I import a calculate rule with an invalid measurement unit in destination
        Then an exception with message "actions[0].destination.unit: The unit \"GIGAHERTZ\" does not exist or does not belong to the default measurement family of the given attribute \"processor\"" has been thrown
