/* global describe, it, expect, spyOn */
'use strict';

define(
    [
        'jquery',
        'underscore',
        'pimee/picker/asset-collection',
        'pim/form-builder',
        'pim/form',
        'pim/fetcher-registry',
        'pim/base-fetcher',
        'backbone/bootstrap-modal'
    ],
    function ($, _, AssetCollection, FormBuilder, Form, fetcherRegistry, Fetcher) {
        describe('Asset collection picker', function () {
            var assetCollection = new AssetCollection();
            _.__ = function (key) {
                return key;
            };

            it('Can store data', function () {
                expect(assetCollection.setData).toBeDefined();

                expect(assetCollection.data).toEqual([]);
                assetCollection.setData(['my_asset']);

                expect(assetCollection.data).toEqual(['my_asset']);
            });

            it('Can store context', function () {
                expect(assetCollection.setContext).toBeDefined();

                expect(assetCollection.context).toEqual({});
                assetCollection.setContext({'key': 'my_asset'});

                expect(assetCollection.context).toEqual({'key': 'my_asset'});
            });

            it('Can be rendered', function () {
                var fetcher = new Fetcher();
                spyOn(fetcherRegistry, 'getFetcher').and.returnValue(fetcher);

                var promise = new $.Deferred();
                promise.resolve([{foo: 'bar'}]);
                spyOn(fetcher, 'fetchByIdentifiers').and.returnValue(promise);
                spyOn(assetCollection, 'template');
                spyOn(assetCollection, 'delegateEvents');
                assetCollection.setData(['my_asset']);
                assetCollection.setContext({
                    scope: 'ecommerce',
                    locale: 'en_US'
                });
                assetCollection.render();

                expect(fetcher.fetchByIdentifiers).toHaveBeenCalledWith(['my_asset']);
                expect(fetcherRegistry.getFetcher).toHaveBeenCalledWith('asset');
                expect(assetCollection.template).toHaveBeenCalledWith({
                    assets: [{foo: 'bar'}],
                    locale: 'en_US',
                    scope: 'ecommerce',
                    thumbnailFilter: 'thumbnail'
                });
            });

            it('Launches the asset management and updates model', function () {
                var promise = new $.Deferred();
                promise.resolve(['my_asset']);
                spyOn(assetCollection, 'manageAssets').and.returnValue(promise);
                spyOn(assetCollection, 'render');
                spyOn(assetCollection, 'trigger');

                assetCollection.updateAssets();

                expect(assetCollection.data).toEqual(['my_asset']);
                expect(assetCollection.manageAssets).toHaveBeenCalled();
                expect(assetCollection.render).toHaveBeenCalled();
                expect(assetCollection.trigger).toHaveBeenCalledWith('collection:change', ['my_asset']);
            });
        });
    }
);
