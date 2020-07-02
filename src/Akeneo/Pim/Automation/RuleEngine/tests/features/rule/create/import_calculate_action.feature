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
    Scenario: Import a calculate rule with invalid round_precision parameter
        When I import a calculate rule with invalid round_precision parameter
        Then an exception with message "actions[0].roundPrecision: This value should be of type int" has been thrown

    @acceptance-back
    Scenario: Import a calculate rule with invalid channels
        When I import a calculate rule with invalid channels
        Then an exception with message "actions[0].destination: The \"item_weight\" attribute requires an existing scope" has been thrown
        And an exception with message "actions[0].source: The \"item_weight\" attribute requires a scope" has been thrown
        And an exception with message "actions[0].operationList[0]: The \"in_stock\" attribute does not require a scope, please remove it" has been thrown

    @acceptance-back
    Scenario: Import a calculate rule with invalid locales
        When I import a calculate rule with invalid locales
        Then an exception with message "actions[0].destination: The \"item_weight\" attribute requires an existing and activated locale, please make sure your locale exists and is activated" has been thrown
        And an exception with message "actions[0].source: The \"item_weight\" attribute requires a locale" has been thrown
        And an exception with message "actions[0].operationList[0]: The \"in_stock\" attribute does not require a locale, please remove it" has been thrown

    @acceptance-back
    Scenario: Import a calculate rule with non numeric attribute types
        When I import a calculate rule with invalid attribute types
        Then an exception with message "actions[0].source.field: The \"description\" attribute has an invalid \"pim_catalog_textarea\" attribute type. A pim_catalog_number | pim_catalog_price_collection | pim_catalog_metric attribute is required" has been thrown
        And an exception with message "actions[0].operationList[0].field: The \"color\" attribute has an invalid \"pim_catalog_simpleselect\" attribute type. A pim_catalog_number | pim_catalog_price_collection | pim_catalog_metric attribute is required" has been thrown
        And an exception with message "actions[0].destination.field: The \"name\" attribute has an invalid \"pim_catalog_text\" attribute type. A pim_catalog_number | pim_catalog_price_collection | pim_catalog_metric attribute is required" has been thrown

    @acceptance-back
    Scenario: Import a calculate rule with invalid currencies
        When I import a calculate rule with invalid currencies
        Then an exception with message "actions[0].destination.currency: The \"currency\" key is missing or empty" has been thrown
        And an exception with message "actions[0].source.currency: A valid currency is required, the \"USD\" currency does not exist or is not activated" has been thrown

    @acceptance-back
    Scenario: Import a calculate rule with invalid measurement unit
        When I import a calculate rule with an invalid measurement unit in destination
        Then an exception with message "actions[0].destination.unit: The \"GIGAHERTZ\" unit code does not exist or does not belong to the measurement family of the \"processor\" attribute" has been thrown
