/* global jasmine, describe, it, expect, spyOn */
'use strict';

define(
    ['jquery', 'pim/entity-manager', 'routing'],
    function ($, EntityManager, Routing) {
        describe('Entity manager', function () {

            it('has a method to load a list of entities', function () {
                expect(EntityManager.getEntityList).toBeDefined();
            });

            it('is aware of entity routes', function () {
                expect(EntityManager.urls.attributegroups).toEqual('pim_enrich_attributegroup_rest_index');
                expect(EntityManager.urls.attributes).toEqual('pim_enrich_attribute_rest_index');
                expect(EntityManager.urls.families).toEqual('pim_enrich_family_rest_index');
                expect(EntityManager.urls.channels).toEqual('pim_enrich_channel_rest_index');
                expect(EntityManager.urls.locales).toEqual('pim_enrich_locale_rest_index');
                expect(EntityManager.urls.measures).toEqual('pim_enrich_measures_rest_index');
                expect(EntityManager.urls.currencies).toEqual('pim_enrich_currency_rest_index');
            });

            it('can load the requested entity list', function () {
                spyOn(Routing, 'generate').and.callThrough();

                spyOn($, 'getJSON').and.returnValue($.Deferred().resolve('foo').promise());

                var result = null;
                EntityManager.getEntityList('attributegroups').done(function (data) {
                    result = data;
                });
                expect(Routing.generate).toHaveBeenCalledWith('pim_enrich_attributegroup_rest_index');
                expect($.getJSON).toHaveBeenCalledWith(jasmine.any(String));

                expect(result).toBe('foo');
            });

            it('caches loaded entity lists', function () {
                spyOn($, 'getJSON');

                var result = null;
                EntityManager.getEntityList('attributegroups').done(function (data) {
                    result = data;
                });
                expect($.getJSON).not.toHaveBeenCalled();
                expect(result).toBe('foo');
            });
        });
    }
);
