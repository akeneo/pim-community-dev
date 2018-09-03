@javascript
Feature: Display the attribute group history
  In order to know who, when and what changes has been made to an attribute group
  As a product manager
  I need to have access to attribute group history

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    And the following attributes:
      | label-en_US | group | type             | code        |
      | Description | other | pim_catalog_text | description |

  @ce
  Scenario: Successfully edit a group and see the history
    Given I am on the attribute group creation page
    And I change the Code to "Technical"
    And I save the group
    And I should see the flash message "Attribute group successfully created"
    When I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | version | property | value     | date |
      | 1       | code     | Technical | now  |
    When I visit the "Properties" tab
    And I fill in the following information:
      | English (United States) | My technical group |
    And I save the group
    And I should see the flash message "Attribute group successfully updated"
    When I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
      | version | property    | value              | date |
      | 1       | code        | Technical          | now  |
      | 2       | label-en_US | My technical group | now  |

    When I visit the "Attributes" tab
    And I add available attributes SKU, Description
    And I save the group
    And I should see the flash message "Attribute group successfully updated"
    When I visit the "History" tab
    Then there should be 3 updates
    And I should see history:
      | version | property    | value              | date |
      | 1       | code        | Technical          | now  |
      | 2       | label-en_US | My technical group | now  |
      | 3       | attributes  | sku,description    | now  |

    When I visit the "Attributes" tab
    And I remove the "Description" attribute
    And I confirm the deletion
    And I save the group
    And I should see the flash message "Attribute group successfully updated"
    When I visit the "History" tab
    Then there should be 4 updates
    And I should see history:
      | version | property    | value              | date |
      | 1       | code        | Technical          | now  |
      | 2       | label-en_US | My technical group | now  |
      | 3       | attributes  | sku,description    | now  |
      | 4       | attributes  | sku                | now  |

  @ce @jira https://akeneo.atlassian.net/browse/PIM-7279
  Scenario: Prevent javascript execution from history tab while updating attribute group label translations
    Given I am on the "other" attribute group page
    And I fill in the following information:
      | English (United States) | <script>document.getElementById('top-page').classList.add('foo');</script> |
    And I save the attribute group
    Then I should see the flash message "Attribute group successfully updated."
    When I visit the "History" tab
    Then I should not see a "#top-page.foo" element
    And I should see history:
      | version | property    | value                                                                                 | date |
      | 4       | label-en_US | \<script\>document\.getElementById\('top-page'\)\.classList\.add\('foo'\);\</script\> | now  |
