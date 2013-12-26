@javascript
Feature: Add/Remove rights to a view
  In order to give/remove some rights to a group of users
  As an admin
  I need to be able to give/remove rights

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "admin"
    
  Scenario: Successfully remove product list rights
    Given I am on the "Administrator" role page
    When I remove rights to the following resources:
      | resources               |
      | List associations       |
      | List categories         |
      | List channels           |
      | List currencies         |
      | List groups             |
      | List group types        |
      | List products           |
      | List product attributes |
    Then I click on "Save" button
    And I have not access to following pages:
      | pages |
      | Settings - Associations |
      | Enrich - Categories     |
      | Spread - Channels       |
      | Settings - Currencies   |
      | Enrich - Groups         |
      | Enrich - Variant groups |
      | Settings - Group types  |
      | Enrich - Products       |
      | Settings - Attributes   |
