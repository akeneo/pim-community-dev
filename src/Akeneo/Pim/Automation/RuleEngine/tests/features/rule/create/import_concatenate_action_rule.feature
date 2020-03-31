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
    Then an exception with message "actions[0].from[1].field: The \"categories\" attribute code does not exist" has been thrown

  @acceptance-back
  Scenario: Import a concatenate rule with unknown target attribute
    When I import a concatenate rule with unknown target attribute
    Then an exception with message "You cannot concatenate data to the \"unknown\" field" has been thrown

  @acceptance-back
  Scenario: Import a concatenate rule with invalid target attribute
    When I import a concatenate rule with non text target attribute
    Then an exception with message "actions[0]: You cannot concatenate data to the \"sku\" field: Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductConcatenateAction" has been thrown
