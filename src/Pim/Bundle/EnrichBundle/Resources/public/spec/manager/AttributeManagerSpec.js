/* global describe, it, expect, spyOn, beforeEach */
'use strict';

define(
    ['jquery', 'pim/attribute-manager', 'pim/fetcher-registry'],
    function ($, attributeManager, FetcherRegistry) {
        describe('Attribute manager', function () {

            beforeEach(function (done) {
                FetcherRegistry.initialize().done(done);
            });

            it('checks identifier not optional', function () {
                expect(attributeManager.isOptional).toBeDefined();
                var identifierAttribute = {type: 'pim_catalog_identifier'};
                var lie = {
                    resolve: function () {
                    }
                };
                spyOn($, 'Deferred').and.returnValue(lie);
                spyOn(lie, 'resolve');
                attributeManager.isOptional(identifierAttribute, {});
                expect(lie.resolve).toHaveBeenCalledWith(false);
            });

            it('checks attribute is optionnal when no family set', function () {
                var attribute = {type: 'pim_catalog_other'};
                var product = {family: null};

                // mock a promise
                var lie = {
                    resolve: function () {
                    }
                };
                spyOn($, 'Deferred').and.returnValue(lie);
                spyOn(lie, 'resolve');
                attributeManager.isOptional(attribute, product);
                // expects the promise resolves to true
                expect(lie.resolve).toHaveBeenCalledWith(true);

                product.family = undefined;
                attributeManager.isOptional(attribute, product);
                expect(lie.resolve).toHaveBeenCalledWith(true);
            });

            it('checks optionnal attribute with family', function () {
                var attribute = {
                    type: 'pim_catalog_other',
                    code: 'not_funny_nor_undead'
                };

                // mock family with 'funny' and 'undead' attribute codes
                var adamsFamily = {
                    code: 'Adams',
                    attributes: [
                        'undead',
                        'funny'
                    ]
                };
                var product = {family: adamsFamily};
                var promiseSpy = {
                    then: function () {
                    }
                };

                spyOn(promiseSpy, 'then');

                var fetcher = FetcherRegistry.getFetcher('family');
                spyOn(fetcher, 'fetch').and.returnValue(promiseSpy);

                attributeManager.isOptional(attribute, product);

                // here we catch the promise callback executed by
                // .then(callback)
                var fetcherCallback = promiseSpy.then.calls.mostRecent().args[0];

                // the attribute code is not 'undead' nor 'funny'
                // so this attribute is optionnal
                expect(fetcherCallback(adamsFamily)).toBeTruthy();

                // if the attribute code is 'funny' or 'undead'
                // then this attribute is not optionnal
                attribute.code = 'funny';
                expect(fetcherCallback(adamsFamily)).toBeFalsy();
                attribute.code = 'undead';
                expect(fetcherCallback(adamsFamily)).toBeFalsy();
            });
        });
    }
);
