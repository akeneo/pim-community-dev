Feature: View channel variations' configurations
  In order to check the transformations of the assets
  As a user
  I need to be able to view channel variations' configurations

  Background:
    Given the "apparel" catalog configuration
    And I am logged in as "Peter"

  @javascript
  Scenario: View the channel variations' configurations
    Given the following CSV file to import:
    """
    channel;configuration
    tablet;{"colorspace":{"colorspace":"gray"},"resize":{"width":100,"height":300}}
    print;{"resolution":{"resolution":72,"resolution-unit":"ppi"},"scale":{"ratio":56}}
    ecommerce;{"thumbnail":{"width":80, "height":120}}
    """
    And the following job "apparel_asset_channel_configuration_import" configuration:
      | filePath | %file to import% |
    When I am on the "apparel_asset_channel_configuration_import" import job page
    And I launch the import job
    And I wait for the "apparel_asset_channel_configuration_import" job to finish
    When I am on the "tablet" channel page
    And I visit the "Asset transformations" tab
    Then I should see the text "Colorspace"
    And I should see the text "gray colorspace"
    And I should see the text "Resize"
    And I should see the text "100px width"
    And I should see the text "300px height"
    When I am on the "print" channel page
    And I visit the "Asset transformations" tab
    Then I should see the text "Resolution"
    And I should see the text "72 ppi"
    And I should see the text "Scale"
    And I should see the text "56%"
    When I am on the "ecommerce" channel page
    And I visit the "Asset transformations" tab
    Then I should see the text "Thumbnail"
    And I should see the text "80px width"
    And I should see the text "120px height"
