@acceptance-back
Feature: Map some attribute options with Franklin attribute options

  Background:
    Given Franklin is configured with a valid token

  Scenario: Successfully retrieve an attribute options mapping
    Given the family "router"
    When I retrieve the attribute options mapping for the family "router" and the attribute "color"
    Then the retrieved attribute options mapping should be:
      | franklin_attribute_id | catalog_attribute_code | status   |
      | color_1               |                        | pending  |
      | color_2               | color2                 | active   |
      | color_3               |                        | inactive |

  Scenario: Successfully retrieve an empty attribute options mapping (happens also on unexisting attribute)
    Given the family "router"
    When I retrieve the attribute options mapping for the family "router" and the attribute "product_weight"
    Then the retrieved attribute options should be empty

  Scenario: Fail to retrieve an attribute options mapping from an unexisting family
    When I retrieve the attribute options mapping for the family "router" and the attribute "color"
    Then an unknown family message should be sent

  Scenario: Fail to retrieve an attribute options mapping when the token is invalid
    Given the family "router"
    And Franklin is configured with an expired token
    When I retrieve the attribute options mapping for the family "router" and the attribute "color"
    Then an authentication error message should be sent

  Scenario: Fail to retrieve an attribute options mapping when Franklin server is down
    Given the family "router"
    And Franklin server is down
    When I retrieve the attribute options mapping for the family "router" and the attribute "color"
    And a data provider error message should be sent
