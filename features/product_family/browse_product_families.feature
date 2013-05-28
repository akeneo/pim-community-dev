Feature: Browser product families
  In order to view the families that have been created
  As an user
  I need to be able to view a list of them

  Scenario: Successfully display all the families
    Given the following families:
      | code       |
      | smartphone |
      | bags       |
      | jewels     |
    And the following family translations:
      | family     | language | label      |
      | jewels     | english  | Jewels     |
      | smartphone | english  | Smartphone |
      | bags       | english  | Bags       |
    And I am logged in as "admin"
    When I am on family page
    Then I should see the families Bags, Jewels and Smartphone
