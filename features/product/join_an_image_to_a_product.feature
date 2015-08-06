@javascript
Feature: Join an image to a product
  In order to join an image to a product
  As a regular user
  I need to be able to upload it and preview it

  Background:
    Given the "default" catalog configuration
    And a "Car" product
    And the following attribute:
      | label  | type  | allowed extensions |
      | Visual | image | jpg                |
    And the "Car" product has the "visual" attribute
    And I am logged in as "Mary"
    And I am on the "Car" product page

  @ce
  Scenario: Successfully leave the image empty
    When I save the product
    Then I should see the text "Product successfully updated"

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

  Scenario: Successfully remove an image if media not on filesystem
    When I attach file "akeneo.jpg" to "Visual"
    And I save the product
    And I delete "Car" media from filesystem
    And I remove the "Visual" file
    And I save the product
    Then I should not see the text "akeneo.jpg"

  Scenario: Successfully replace an image
    When I attach file "akeneo.jpg" to "Visual"
    And I save the product
    And I remove the "Visual" file
    And I attach file "akeneo2.jpg" to "Visual"
    And I save the product
    Then I should not see the text "akeneo.jpg"
    But I should see the text "akeneo2.jpg"

  Scenario: Successfully replace and remove an image
    When I attach file "akeneo.jpg" to "Visual"
    And I save the product
    And I remove the "Visual" file
    And I attach file "akeneo2.jpg" to "Visual"
    And I save the product
    Then I should not see the text "akeneo.jpg"
    But I should see the text "akeneo2.jpg"
