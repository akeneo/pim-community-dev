/* global jasmine, describe, it, expect, spyOn */
'use strict';

define(
    [
        'jquery',
        'pimee/mass-product-edit-form/locale-switcher',
        'pim/attribute-manager',
        'pim/fetcher-registry'
    ],
    function (
        $,
        LocaleSwitcher,
        AttributeManager,
        FetcherRegistry
    ) {
        describe('Mass edit locale switcher enterprise module', function () {

            it('displays only editable locales', function () {
                var serverLocales = [
                    {code: 'en_US'},
                    {code: 'fr_FR'},
                    {code: 'es_ES'}
                ];

                var serverPermission = {
                    'locales': [
                        {code: 'en_US', edit: true, view: true},
                        {code: 'fr_FR', edit: true, view: true},
                        {code: 'es_ES', edit: false, view: true}
                    ]
                };

                var expectedLocales = [
                    {code: 'en_US'},
                    {code: 'fr_FR'}
                ];

                var localeFetcher = jasmine.createSpyObj('localeFetcher', ['fetchAll']);
                localeFetcher.fetchAll.and.returnValue(serverLocales);

                var permissionFetcher = jasmine.createSpyObj('permissionFetcher', ['fetchAll']);
                permissionFetcher.fetchAll.and.returnValue(serverPermission);

                spyOn(FetcherRegistry, 'getFetcher').and.callFake(function (name) {
                    var fetchers = {
                        'locale': localeFetcher,
                        'permission': permissionFetcher
                    };

                    return fetchers[name];
                });

                var localeSwitcher = new LocaleSwitcher();
                var deferred = localeSwitcher.getDisplayedLocales();

                deferred.done(function (locales) {
                    expect(locales).toEqual(expectedLocales);
                });
            });
        });
    }
);
