/* global jasmine, describe, it, expect, spyOn */
'use strict';

define(
    ['jquery', 'pim/entity-repository', 'routing'],
    function ($, EntityRepository, Routing) {
        describe('Entity repository', function () {

            var urls = {
                list: 'list_route'
            };

            var repository = new EntityRepository({ urls: urls });

            it('provides a method to list all entities', function () {
                expect(repository.findAll).toBeDefined();
            });

            it('provides a method to get a single entity', function () {
                expect(repository.find).toBeDefined();
            });

            it('provides a method to clear cached results', function () {
                expect(repository.clear).toBeDefined();
            });

            it('provides an extension point', function () {
                expect(EntityRepository.extend).toBeDefined();
            });

            it('can load the requested entity list', function () {
                spyOn(Routing, 'generate').and.returnValue('list');

                spyOn($, 'getJSON').and.returnValue($.Deferred().resolve('foo').promise());

                var result = null;
                repository.findAll().done(function (data) {
                    result = data;
                });
                expect(Routing.generate).toHaveBeenCalledWith('list_route');
                expect($.getJSON).toHaveBeenCalledWith(jasmine.any(String));

                expect(result).toBe('foo');
            });

            it('caches loaded entity lists', function () {
                spyOn($, 'getJSON');

                var result = null;
                repository.findAll().done(function (data) {
                    result = data;
                });
                expect($.getJSON).not.toHaveBeenCalled();
                expect(result).toBe('foo');
            });
        });
    }
);
