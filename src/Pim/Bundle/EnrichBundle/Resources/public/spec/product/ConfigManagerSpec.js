/* global jasmine, describe, it, expect, spyOn */
'use strict';

define(
    ['jquery', 'pim/config-manager', 'routing'],
    function ($, ConfigManager, Routing) {
        describe('Config manager', function () {

            it('has a method to load a list of entities', function () {
                expect(ConfigManager.getEntityList).toBeDefined();
            });

            it('is aware of entity routes', function () {
                expect(ConfigManager.urls.attributegroups).toEqual('pim_enrich_attributegroup_rest_index');
                expect(ConfigManager.urls.attributes).toEqual('pim_enrich_attribute_rest_index');
                expect(ConfigManager.urls.families).toEqual('pim_enrich_family_rest_index');
                expect(ConfigManager.urls.channels).toEqual('pim_enrich_channel_rest_index');
                expect(ConfigManager.urls.locales).toEqual('pim_enrich_locale_rest_index');
                expect(ConfigManager.urls.measures).toEqual('pim_enrich_measures_rest_index');
                expect(ConfigManager.urls.currencies).toEqual('pim_enrich_currency_rest_index');
            });

            it('can load the requested entity list', function () {
                spyOn(Routing, 'generate').and.callThrough();

                var promise = $.Deferred();
                promise.resolve('foo');
                spyOn($, 'ajax').and.returnValue(promise.promise());

                var result = null;
                ConfigManager.getEntityList('attributegroups').done(function (data) {
                    result = data;
                });
                expect(Routing.generate).toHaveBeenCalledWith('pim_enrich_attributegroup_rest_index');
                expect($.ajax).toHaveBeenCalledWith(
                    jasmine.any(String),
                    jasmine.objectContaining({
                        method: 'GET'
                    })
                );

                expect(result).toBe('foo');
            });

            it('caches loaded entity lists', function () {
                spyOn($, 'ajax');

                var result = null;
                ConfigManager.getEntityList('attributegroups').done(function (data) {
                    result = data;
                });
                expect($.ajax).not.toHaveBeenCalled();
                expect(result).toBe('foo');
            });
        });
    }
);
