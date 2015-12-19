/* global jasmine, describe, it, expect, spyOn */
'use strict';

define(
    [
        'pim/mass-product-edit-form/add-attribute',
        'pim/attribute-manager',
        'pim/fetcher-registry'
    ],
    function (
        AddAttribute,
        AttributeManager,
        FetcherRegistry
    ) {
        describe('Mass edit add attribute module', function () {

            it('removes unique attributes from select list', function () {
                var serverAttributes = [
                    {code: 'description', unique: 0},
                    {code: 'sku', unique: 1},
                    {code: 'code', unique: 1},
                    {code: 'name', unique: 0}
                ];

                var expectedAttributes = [
                    {code: 'description', unique: 0},
                    {code: 'name', unique: 0}
                ];

                var groupFetcher = jasmine.createSpyObj('groupFetcher', ['fetchAll']);
                var addAttribute = new AddAttribute();

                spyOn(AttributeManager, 'getAvailableOptionalAttributes').and.returnValue(serverAttributes);
                spyOn(FetcherRegistry, 'getFetcher').and.returnValue(groupFetcher);
                spyOn(addAttribute, 'initializeSelect');
                spyOn(addAttribute, 'getFormData').and.returnValue(null);

                addAttribute.loadAttributesChoices();

                expect(addAttribute.initializeSelect.calls.mostRecent().args[0])
                    .toEqual(expectedAttributes, jasmine.any(Array));
            });
        });
    }
);
