Feature: Import clear action rules
  In order ease the enrichment of the catalog
  As an administrator
  I need to be able to create rules

  Background:
    Given the family "camcorders"
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
    And an exception with message "actions[0]: You cannot clear the data from the \"unknown\" field.: Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductClearAction" has been thrown

  @acceptance-back
  Scenario: Import a clear rule with bad locale
    When I import a clear rule with localized attribute and without locale
    And an exception with message "actions[0]: The \"name\" attribute code is localizable and no locale is provided.: Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductClearAction" has been thrown
