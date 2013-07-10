@javascript
Feature: Display channels
  In order to manage channels
  As a user
  I need to be able to see a list of channels and their properties

  Background:
    Given the following categories:
      | code           | title          |
      | ipad_catalog   | iPad Catalog   |
      | mobile_catelog | Mobile Catalog |
      | master_catelog | Master Catalog |
    And the following channels:
      | code              | name      | category       |
      | ipad_channel      | iPad      | iPad Catalog   |
      | mobile_channel    | Mobile    | Mobile Catalog |
      | ecommerce_channel | eCommerce | Master Catalog |
    And I am logged in as "admin"

  Scenario: Succesfully display channels
    When I am on the channels page
    Then I should see channels iPad, Mobile and eCommerce
    And the channel iPad is able to export category iPad Catalog
    But the channel Mobile is not able to export category Master Catalog
