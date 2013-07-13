@javascript
Feature: Display channels
  In order to manage channels
  As a user
  I need to be able to see a list of channels and their properties

  Background:
    Given the following categories:
      | code           | title          |
      | ipad_catalog   | iPad Catalog   |
      | mobile_catalog | Mobile Catalog |
      | master_catalog | Master Catalog |
    And the following channels:
      | code              | name      | category       |
      | ipad_channel      | iPad      | ipad_catalog   |
      | mobile_channel    | Mobile    | mobile_catalog |
      | ecommerce_channel | eCommerce | master_catalog |
    And I am logged in as "admin"

  Scenario: Succesfully display channels
    When I am on the channels page
    Then I should see channels iPad, Mobile and eCommerce
    And the channel iPad is able to export category iPad Catalog
    But the channel Mobile is not able to export category Master Catalog
