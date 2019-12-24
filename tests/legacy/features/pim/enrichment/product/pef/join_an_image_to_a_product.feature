@javascript
Feature: Join an image to a product
  In order to join an image to a product
  As a regular user
  I need to be able to upload it and preview it

  Background:
    Given the "default" catalog configuration
    And the following attribute:
      | label-en_US | type              | allowed_extensions | group | code   |
      | Visual      | pim_catalog_image | jpg,gif            | other | visual |
    And the following family:
      | code     | attributes |
      | vehicles | sku,visual |
    And the following product:
      | sku | family   |
      | Car | vehicles |
    And I am logged in as "Mary"
    And I am on the "Car" product page

  @critical
  Scenario: Successfully upload an image
    When I attach file "akeneo.jpg" to "Visual"
    And I save the product
    Then I should see the text "akeneo.jpg"

  Scenario: Successfully display the image in a popin
    When I attach file "akeneo.jpg" to "Visual"
    And I save the product
    And I open "akeneo.jpg" in the current window
    Then I should see the uploaded image

  Scenario: Successfully remove an image
    When I attach file "akeneo.jpg" to "Visual"
    And I save the product
    And I remove the "Visual" file
    And I save the product
    Then I should not see the text "akeneo.jpg"

  Scenario: Successfully replace an image
    When I attach file "akeneo.jpg" to "Visual"
    And I save the product
    And I remove the "Visual" file
    And I attach file "bic-core-148.gif" to "Visual"
    And I save the product
    Then I should not see the text "akeneo.jpg"
    But I should see the text "bic-core-148.gif"
