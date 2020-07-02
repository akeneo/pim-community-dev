Feature: Import clear action rules
  In order ease the enrichment of the catalog
  As an administrator
  I need to be able to create rules

  Background:
    Given the following locales en_US
    And the following ecommerce channel with locales en_US
    And the product "75024" of the family "camcorders"
    And I have permission to import rules

  @acceptance-back
  Scenario: Import a clear rule for valid attributes
    When I import a valid clear rule
    Then no exception has been thrown
    And the rule list contains the valid clear rule

  @acceptance-back
  Scenario: Import a clear rule with unknown attributes
    When I import a clear rule with unknown attribute
    And an exception with message "actions[0].field: You cannot clear data from the \"unknown\" field" has been thrown

  @acceptance-back
  Scenario: Import a clear rule with bad locale
    When I import a clear rule with localized attribute and without locale
    And an exception with message "actions[0]: Attribute \"name\" expects a locale, none given." has been thrown
