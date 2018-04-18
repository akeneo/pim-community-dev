@javascript
Feature: Display the association type history
  In order to know who, when and what changes has been made to an association type
  As a product manager
  I need to have access to an association history

  Scenario: Successfully edit an association type and see the history
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the association types page
    When I create a new association type
    And I fill in the following information in the popin:
      | Code | REPLACEMENT |
    And I press the "Save" button
    And I am on the association types page
    Then I should see the text "REPLACEMENT"
    When I am on the "REPLACEMENT" association type page
    And I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | version | property | value       | date |
      | 1       | code     | REPLACEMENT | now  |

  @javascript @jira https://akeneo.atlassian.net/browse/PIM-7279
  Scenario: Prevent javascript execution from history tab while updating association type label translations
    Given a "default" catalog configuration
    And I am logged in as "Julia"
    And I am on the "Pack" association type page
    And I fill in the following information:
      | English (United States) | <script>document.getElementById('top-page').classList.add('foo');</script> |
    And I save the association type
    Then I should see the flash message "Association type successfully updated."
    When I visit the "History" tab
    Then I should not see a "#top-page.foo" element
    And I should see history:
      | version | property    | value                                                                                 | date |
      | 2       | label-en_US | \<script\>document\.getElementById\('top-page'\)\.classList\.add\('foo'\);\</script\> | now  |
