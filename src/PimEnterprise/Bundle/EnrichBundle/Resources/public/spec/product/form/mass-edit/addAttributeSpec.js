/* global jasmine, describe, it, expect, spyOn */
'use strict';

define(
    [
        'pimee/mass-product-edit-form/add-attribute',
        'pim/attribute-manager',
        'pim/fetcher-registry'
    ],
    function (
        AddAttribute,
        AttributeManager,
        FetcherRegistry
    ) {
        describe('Mass edit add attribute enterprise module', function () {

            it('removes unique and not editable attributes from select list', function () {
                var serverAttributes = [
                    {code: 'description', unique: 0, group: 'marketing'},
                    {code: 'sku', unique: 1, group: 'marketing'},
                    {code: 'code', unique: 1, group: 'technical'},
                    {code: 'name', unique: 0, group: 'book'},
                    {code: 'picture', unique: 0, group: 'media'},
                    {code: 'weight', unique: 0, group: 'technical'}
                ];

                var serverPermission = {
                    'attribute_groups': [
                        {code: 'marketing', edit: true, view: true},
                        {code: 'book', edit: true, view: true},
                        {code: 'technical', edit: false, view: true},
                        {code: 'media', edit: false, view: true}
                    ]
                };

                var expectedAttributes = [
                    {code: 'description', unique: 0, group: 'marketing'},
                    {code: 'name', unique: 0, group: 'book'}
                ];

                var groupFetcher = jasmine.createSpyObj('groupFetcher', ['fetchAll']);
                var permissionFetcher = jasmine.createSpyObj('permissionFetcher', ['fetchAll']);
                permissionFetcher.fetchAll.and.returnValue(serverPermission);
                var addAttribute = new AddAttribute();

                spyOn(AttributeManager, 'getAvailableOptionalAttributes').and.returnValue(serverAttributes);
                spyOn(FetcherRegistry, 'getFetcher').and.callFake(function (name) {
                    var fetchers = {
                        'attribute-group': groupFetcher,
                        'permission': permissionFetcher
                    };

                    return fetchers[name];
                });
                spyOn(addAttribute, 'initializeSelect');
                spyOn(addAttribute, 'getFormData').and.returnValue(null);

                addAttribute.loadAttributesChoices();

                expect(addAttribute.initializeSelect.calls.mostRecent().args[0])
                    .toEqual(expectedAttributes, jasmine.any(Array));
            });
        });
    }
);
