@acceptance-back
Feature: Configure the connection to PIM.ai
  In order to automatically enrich products
  As a system administrator
  I want to setup the PIM connection to PIM.ai

  Scenario: Setup the connection to PIM.ai
    When a system administrator tries to connect Akeneo PIM to PIM.ai
    Then Akeneo PIM connection to PIM.ai is activate

  Scenario: Reactivate the connection to PIM.ai
    Given Akeneo PIM is not connected to PIM.ai anymore
    When a system administrator tries to reconnect Akeneo PIM to PIM.ai
    Then Akeneo PIM connection to PIM.ai is activate

  Scenario: Cannot setup a connection to PIM.ai with an invalid token
    When a system administrator tries to connect Akeneo PIM to PIM.ai with an invalid activation code
    Then Akeneo PIM connection to PIM.ai is not activate

  Scenario: Retrieve an activated connection
    Given Akeneo PIM is connected to PIM.ai
    Then PIM.ai configuration can be retrieved
