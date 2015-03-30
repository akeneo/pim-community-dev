/* global jasmine, describe, it, expect, spyOn */
'use strict';

define(
    ['jquery', 'pim/association-manager', 'routing'],
    function($, AssociationManager, Routing) {
        describe('Association manager', function() {

            it('has a method to load the list of association types', function() {
                expect(AssociationManager.getAssociationTypes).toBeDefined();
            });

            it('can load the list of association types', function() {
                spyOn(Routing, 'generate').and.callThrough();

                var promise = $.Deferred();
                promise.resolve({data : ['foo']});
                spyOn($, 'getJSON').and.returnValue(promise.promise());

                var result = null;
                AssociationManager.getAssociationTypes().done(function (data) {
                    result = data;
                });
                expect(Routing.generate).toHaveBeenCalled();
                expect($.getJSON).toHaveBeenCalledWith(jasmine.any(String));

                expect(result).toEqual(['foo']);
            });

            it('caches an already loaded list', function() {
                spyOn($, 'getJSON');

                var result = null;
                AssociationManager.getAssociationTypes().done(function (data) {
                    console.log(data);
                    result = data;
                });
                expect($.getJSON).not.toHaveBeenCalled();
                expect(result).toEqual(['foo']);
            });
        }
    );
});
