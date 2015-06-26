/* global jasmine, describe, it, expect, spyOn */
'use strict';

define(
    ['jquery', 'pim/base-fetcher', 'routing'],
    function ($, BaseFetcher, Routing) {
        describe('Base fetcher', function () {

            var urls = {
                list: 'list_route'
            };

            var fetcher = new BaseFetcher({ urls: urls });

            it('provides a method to list all entities', function () {
                expect(fetcher.fetchAll).toBeDefined();
            });

            it('provides a method to get a single entity', function () {
                expect(fetcher.fetch).toBeDefined();
            });

            it('provides a method to get a collection of entities', function () {
                expect(fetcher.fetchByIdentifiers).toBeDefined();
            });

            it('provides a method to clear cached results', function () {
                expect(fetcher.clear).toBeDefined();
            });

            it('provides an extension point', function () {
                expect(BaseFetcher.extend).toBeDefined();
            });

            it('can load the requested entity list', function () {
                spyOn(Routing, 'generate').and.returnValue('list');

                spyOn($, 'getJSON').and.returnValue($.Deferred().resolve('foo').promise());

                var result = null;
                fetcher.fetchAll().done(function (data) {
                    result = data;
                });
                expect(Routing.generate).toHaveBeenCalledWith('list_route');
                expect($.getJSON).toHaveBeenCalledWith(jasmine.any(String));

                expect(result).toBe('foo');
            });

            it('caches loaded entity lists', function () {
                spyOn($, 'getJSON');

                var result = null;
                fetcher.fetchAll().done(function (data) {
                    result = data;
                });
                expect($.getJSON).not.toHaveBeenCalled();
                expect(result).toBe('foo');
            });
        });
    }
);
