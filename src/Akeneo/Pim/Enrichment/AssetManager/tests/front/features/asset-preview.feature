Feature: Show an asset preview
    In order to see the preview of the assets in my collection
    As a user
    I want to be able to open the preview and navigate between the assets

    @acceptance-front
    Scenario: Open the asset preview on first asset
        Given an asset collection with three assets
        And the user go to the asset tab
        When the user open the first asset preview
        Then the preview should be displayed
        And the first asset should be displayed
