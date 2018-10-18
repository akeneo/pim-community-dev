@acceptance-back
Feature: Map some attribute options with PIM.ai attribute options

  Scenario: Successfully retrieve the attribute options mapping
    Given the family "router"
    And PIM.ai is configured with a valid token
    When I retrieved the attribute options mapping for the family "router" and the attribute "color"
    Then the retrieved attribute options mapping should be
