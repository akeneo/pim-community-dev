Feature: Delete an attribute linked to an asset family
  In order to modify an asset family
  As a user
  I want delete an attribute linked to an asset family

  @acceptance-back
  Scenario: Delete a text attribute linked to an asset family
    Given a valid asset family
    And the following text attributes:
      | entity_identifier | code | labels                                    | required | read_only | order | value_per_channel | value_per_locale | max_length |
      | designer          | name | {"en_US": "Stylist", "fr_FR": "Styliste"} | true     | false     | 2     | true              | false            | 44         |
    When the user deletes the attribute "name" linked to the asset family "designer"
    Then there is no attribute "name" for the asset family "designer"

  @acceptance-back
  Scenario: Cannot delete the attribute as label linked to an asset family
    Given a valid asset family
    Then it is not possible to delete the attribute as label linked to this entity

  @acceptance-back
  Scenario: Cannot delete the attribute as main media linked to an asset family
    Given a valid asset family
    Then it is not possible to delete the attribute as main media linked to this entity

  @acceptance-front
  Scenario: Delete a text attribute linked to an asset family
    Given a valid asset family
    And the user has the following rights:
      | akeneo_assetmanager_attribute_edit   | true |
      | akeneo_assetmanager_attribute_delete | true |
    And the user asks for the asset family "designer"
    Then there should be the following attributes:
      | code     | type  |
      | name     | text  |
      | bio      | text  |
      | portrait | media_file |
    When the user deletes the attribute "bio" linked to the asset family "designer"
    And the user should see the deleted notification
    Then there should be the following attributes:
      | code     | type  |
      | name     | text  |
      | portrait | media_file |

#  @acceptance-front
  Scenario: Cannot delete a text attribute linked to an asset family
    Given a valid asset family
    And the user has the following rights:
      | akeneo_assetmanager_attribute_edit   | true |
      | akeneo_assetmanager_attribute_delete | true |
    And the user asks for the asset family "designer"
    Then there should be the following attributes:
      | code     | type  |
      | name     | text  |
      | bio     | text  |
      | portrait | media_file |
    When the user cannot delete the attribute "bio" linked to the asset family "designer"
    Then the user should see the delete notification error
    And there should be the following attributes:
      | code     | type  |
      | name     | text  |
      | bio     | text  |
      | portrait | media_file |

#  @acceptance-front
  Scenario: User doesn't have the right to delete a text attribute linked to an asset family
    Given a valid asset family
    And the user has the following rights:
      | akeneo_assetmanager_attribute_edit   | false |
      | akeneo_assetmanager_attribute_delete | false |
    And the user asks for the asset family "designer"
    Then there should be the following attributes:
      | code     | type  |
      | name     | text  |
      | portrait | media_file |
    Then the user cannot delete the attribute "name"

  @acceptance-front
  Scenario: Cancel a text attribute deletion
    Given a valid asset family
    And the user has the following rights:
      | akeneo_assetmanager_attribute_edit   | true |
      | akeneo_assetmanager_attribute_delete | true |
    And the user asks for the asset family "designer"
    Then there should be the following attributes:
      | code     | type  |
      | name     | text  |
      | bio     | text  |
      | portrait | media_file |
    When the user cancel the deletion of attribute "bio"
    And there should be the following attributes:
      | code     | type  |
      | name     | text  |
      | bio     | text  |
      | portrait | media_file |
