Feature: Import concatenate action rules
  In order ease the enrichment of the catalog
  As an administrator
  I need to be able to create rules

  Background:
    Given the following locales en_US,fr_FR
    And the following ecommerce channel with locales en_US,fr_FR
    And the family "camcorders"
    And the product "75024" of the family "camcorders"
    And I have permission to import rules

  @acceptance-back
  Scenario: Import a concatenate rule with valid values for attribute of type text and text in actions
    When I import a valid concatenate rule
    Then no exception has been thrown
    And the rule list contains the imported concatenate rule

  @acceptance-back
  Scenario: Import a concatenate rule with invalid source attributes
    When I import a concatenate rule with invalid source attributes
    And an exception with message "actions[0].from[1].field: The \"categories\" attribute does not exist" has been thrown
    And an exception with message "actions[0].from[3]: A single key among \"field\", \"text\" or \"new_line\" is required, please select only one" has been thrown
    And an exception with message "actions[0].from[4]: A single key among \"field\", \"text\" or \"new_line\" is required, please select only one" has been thrown
    And an exception with message "actions[0].from[5]: A single key among \"field\", \"text\" or \"new_line\" is required, please select only one" has been thrown
    And an exception with message "actions[0].from[6]: A single key among \"field\", \"text\" or \"new_line\" is required, please select one" has been thrown
    And an exception with message "actions[0].from[7]: The \"processor\" attribute requires an existing and activated locale, please make sure your locale exists and is activated: unknown1" has been thrown

  @acceptance-back
  Scenario: Import a concatenate rule with missing fields
    When I import a concatenate rule with missing from and to keys
    Then an exception with message "actions[0].from: The \"from\" key is missing or empty" has been thrown
    And an exception with message "actions[0].to: The \"to\" key is missing or empty" has been thrown

  @acceptance-back
  Scenario: Import a concatenate rule with unknown target attribute
    When I import a concatenate rule with unknown target attribute
    Then an exception with message "actions[0].to.field: The \"unknown\" attribute does not exist" has been thrown

  @acceptance-back
  Scenario: Import a concatenate rule with invalid target attribute
    When I import a concatenate rule with non text target attribute
    Then an exception with message "actions[0].to.field: The \"sku\" attribute has an invalid \"pim_catalog_identifier\" attribute type. A pim_catalog_text | pim_catalog_textarea attribute is required" has been thrown

  @acceptance-back
  Scenario: Import a concatenate rule with new line and a text target attribute
    When I import a concatenate rule with new line and a text target attribute
    Then an exception with message "actions[0].from[1].new_line: The \"name\" target attribute does not accept new line" has been thrown
    And an exception with message "actions[0].from[3].new_line: The \"name\" target attribute does not accept new line" has been thrown
