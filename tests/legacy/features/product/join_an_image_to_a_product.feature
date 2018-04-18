@javascript
Feature: Join an image to a product
  In order to join an image to a product
  As a regular user
  I need to be able to upload it and preview it

  Background:
    Given the "default" catalog configuration
    And a "Car" product
    And the following attribute:
      | label-en_US | type              | allowed_extensions | group | code   |
      | Visual      | pim_catalog_image | jpg,gif            | other | visual |
    And the "Car" product has the "visual" attribute
    And I am logged in as "Mary"
    And I am on the "Car" product page

  @ce
  Scenario: Successfully leave the image empty
    When I save the product
    Then I should see the flash message "Product successfully updated"

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
    And I attach file "bic-core-148.gif" to "Visual"
    And I save the product
    Then I should not see the text "akeneo.jpg"
    But I should see the text "bic-core-148.gif"

  @jira https://akeneo.atlassian.net/browse/PIM-5712
  Scenario: Successfully remove an image then its field and keep a reference to the file in database
    When I attach file "akeneo.jpg" to "Visual"
    And I save the product
    And I remove the "Visual" file
    And I save the product
    Then I should not see the text "akeneo.jpg"
    When I remove the "Visual" attribute
    And I confirm the deletion
    And I save the product
    Then The file with original filename "akeneo.jpg" should exists in database

  @jira https://akeneo.atlassian.net/browse/PIM-5712
  Scenario: Successfully remove an image field containing an image and keep a reference to the file in database
    When I attach file "akeneo.jpg" to "Visual"
    And I save the product
    And I remove the "Visual" attribute
    And I confirm the deletion
    And I save the product
    Then The file with original filename "akeneo.jpg" should exists in database
