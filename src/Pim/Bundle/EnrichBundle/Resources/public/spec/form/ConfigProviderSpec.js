/* global describe, it, expect, spyOn */
'use strict';

define(
    ['pim/form-config-provider', 'jquery', 'routing'],
    function (ConfigProvider, $, Routing) {
        describe('Form config provider', function () {

            it('can load extension map and attribute fields', function () {
                expect(ConfigProvider.getExtensionMap).toBeDefined();
                expect(ConfigProvider.getAttributeFields).toBeDefined();
            });

            it('returns attribute fields', function () {
                spyOn(Routing, 'generate').and.returnValue('url');
                spyOn($, 'getJSON').and.returnValue($.Deferred().resolve({
                    extensions: 'extensions',
                    attribute_fields: 'attribute_fields'
                }).promise());

                var result = null;
                ConfigProvider.getAttributeFields().done(function (data) {
                    result = data;
                });
                expect(result).toBe('attribute_fields');
                expect(Routing.generate).toHaveBeenCalled();
            });

            it('returns extension map', function () {
                spyOn(Routing, 'generate').and.returnValue('url');
                spyOn($, 'getJSON').and.returnValue($.Deferred().resolve({
                    extensions: 'extensions',
                    attribute_fields: 'attribute_fields'
                }).promise());

                var result = null;
                ConfigProvider.getExtensionMap().done(function (data) {
                    result = data;
                });
                expect(result).toBe('extensions');
            });
        });
    }
);
