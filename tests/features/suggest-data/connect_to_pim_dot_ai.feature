Feature: Connect the PIM to PIM.ai
  In order to automatically enrich my products
  As a system administrator
  I want to setup the PIM connection to PIM.ai

  @acceptance-back
  Scenario: Setup the connection to PIM.ai
    When a valid activation code is used to activate PIM.ai connection
    Then the PIM.ai connection is activated

  @acceptance-back
  Scenario: Cannot setup a connection to PIM.ai with an invalid token
    When an invalid activation code is used to activate PIM.ai connection
    Then the PIM.ai connection is not activated

  @acceptance-back
  Scenario: Retrieve an activated connection
    When PIM.ai connection is activated
    Then I can retrieve the configuration of PIM.ai connection
