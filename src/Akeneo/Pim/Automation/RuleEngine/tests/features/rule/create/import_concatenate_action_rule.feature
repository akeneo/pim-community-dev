Feature: Import concatenate action rules
  In order ease the enrichment of the catalog
  As an administrator
  I need to be able to create rules

  Background:
    Given the family "camcorders"
    And the product "75024" of the family "camcorders"
    And I have permission to import rules

  @acceptance-back
  Scenario: Import a concatenate rule with valid values for attribute of type text and text in actions
    When I import a valid concatenate rule
    Then no exception has been thrown
    And the rule list contains the valid concatenate rule

  @acceptance-back
  Scenario: Import a concatenate rule with invalid source attributes
    When I import a concatenate rule with invalid source attributes
    And an exception with message "actions[0]: The \"categories\" attribute code do not exist.: Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductConcatenateAction" has been thrown

  @acceptance-back
  Scenario: Import a concatenate rule with unknown target attribute
    When I import a concatenate rule with unknown target attribute
    And an exception with message "actions[0]: The \"unknown\" attribute code do not exist.: Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductConcatenateAction" has been thrown

  @acceptance-back
  Scenario: Import a concatenate rule with invalid target attribute
    When I import a concatenate rule with non text target attribute
    And an exception with message "actions[0]: You cannot concatenate data to the \"sku\" field.: Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductConcatenateAction" has been thrown
