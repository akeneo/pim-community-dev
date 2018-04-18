@javascript
Feature: Display an asset collection proposal in datagrid
  In order to summarize proposals
  As a product manager
  I need to be able to view a proposal

  Background:
    Given a "clothing" catalog configuration
    And the product:
      | family     | pants           |
      | categories | 2014_collection |
      | sku        | mon-froc        |
    And I am logged in as "Peter"
    And I am on the "Pants" family page
    And I visit the "Attributes" tab
    And I add available attributes gallery
    And I switch the attribute "gallery" requirement in channel "tablet"
    And I save the family
    And I logout

  @jira https://akeneo.atlassian.net/browse/PIM-7269
  Scenario: Successfully list proposals on an asset collection
    Given I am logged in as "Sandra"
    And I am on the "mon-froc" product page
    When I visit the "Media" group
    And I start to manage assets for "gallery"
    And I check the row "paint"
    And I confirm the asset modification
    And I save the product
    And I press the Send for approval button
    And I logout
    And I am logged in as "Julia"
    And I am on the proposals page
    Then I should see the following proposals:
      | product  | author | attribute | original | new               |
      | mon-froc | Sandra | gallery   |          | Photo of a paint. |
