@javascript
Feature: Display the attribute history
  In order to know who, when and what changes has been made to an attribute
  As a product manager
  I need to have access to a attribute history

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully edit a attribute and see the history
    Given I am on the attributes page
    And I create a "Simple select" attribute
    And I change the "Code" to "packaging"
    And I change the "Attribute group" to "Other"
    And I save the attribute
    Then I should not see the text "There are unsaved change"
    And I visit the "Options" tab
    And I create the following attribute options:
      | Code        |
      | classic_box |
    And I save the attribute
    Then I should not see the text "There are unsaved change"
    When I visit the "History" tab
    Then there should be 2 update
    And I should see history:
      | version | property | value     | date |
      | 1       | code     | packaging | now  |
    And I visit the "Options" tab
    And I create the following attribute options:
      | Code      |
      | collector |
    And I save the attribute
    Then I should not see the text "There are unsaved change"
    When I visit the "History" tab
    Then there should be 3 updates

  @javascript @jira https://akeneo.atlassian.net/browse/PIM-7279
  Scenario: Prevent javascript execution from history tab while updating attribute label translations
    Given the following attribute:
      | label-en_US | type             | useable_as_grid_filter | localizable | group | code |
      | name        | pim_catalog_text | 1                      | 1           | other | name |
    And I am on the "name" attribute page
    And I visit the "Label translations" tab
    And I fill in the following information:
      | English (United States) | <script>document.getElementById('top-page').classList.add('foo');</script> |
    And I save the attribute
    Then I should see the flash message "Attribute successfully updated."
    When I visit the "History" tab
    Then I should not see a "#top-page.foo" element
    And I should see history:
      | version | property    | value                                                                                 | date |
      | 2       | label-en_US | \<script\>document\.getElementById\('top-page'\)\.classList\.add\('foo'\);\</script\> | now  |
