/* global describe, beforeEach, it, expect, spyOn */
'use strict';

define(
    [
        'jquery',
        'pimee/product-edit-form/attributes/copy',
        'pim/fetcher-registry',
        'pimee/product-draft-fetcher'
    ],
    function (
        $,
        Copy,
        FetcherRegistry,
        Fetcher
    ) {
        describe('Copy extension override', function () {

            var copy;
            var fetcher;

            beforeEach(function () {
                copy = new Copy();
                fetcher = new Fetcher();

                spyOn(FetcherRegistry, 'getFetcher').and.returnValue(fetcher);
                spyOn(fetcher, 'clear');
            });

            it('has methods', function () {
                expect(copy.configure).toBeDefined();
                expect(copy.render).toBeDefined();
                expect(copy.getSourceData).toBeDefined();
                expect(copy.canBeCopied).toBeDefined();

                expect(copy.ensureSwitcherContext).toBeDefined();
                expect(copy.changeCurrentSource).toBeDefined();
                expect(copy.startCopyingWorkingCopy).toBeDefined();
            });

            it('clear the fetcher cache during configure', function () {
                copy.configure();

                expect(fetcher.clear).toHaveBeenCalled();
            });

            it('set default sources during initialize', function () {
                expect(copy.sources).toContain({
                    'code': 'working_copy',
                    'label': 'pimee_enrich.entity.product.copy.source.working_copy',
                    'type': 'working_copy',
                    'author': null
                });

                expect(copy.sources).toContain({
                    'code': 'draft',
                    'label': 'pimee_enrich.entity.product.copy.source.draft',
                    'type': 'my_draft',
                    'author': null
                });
            });

            it('add drafts to sources during render', function () {
                var draft = {
                    'author': 'mary',
                    'changes': {
                        'values': {
                            'name': [
                                {'scope': null, 'locale': null, 'data': 'A name'}
                            ]
                        }
                    }
                };

                var promise = $.Deferred().resolve([draft]).promise();

                spyOn(fetcher, 'fetchAllByProduct').and.returnValue(promise);

                copy.configure();
                copy.setData({meta: {id: 42}});
                copy.startCopying();
                copy.render();

                expect(fetcher.fetchAllByProduct.calls.count()).toEqual(1);
                expect(fetcher.fetchAllByProduct.calls.argsFor(0)).toEqual([42]);
                expect(copy.otherDrafts).toContain(draft);
                expect(copy.sources).toContain({
                    'code': 'draft_of_mary',
                    'label': 'pimee_enrich.entity.product.copy.source.draft_of',
                    'type': 'draft',
                    'author': 'mary'
                });
            });

            it('updates current source and trigger context change event on a show working copy event', function () {
                spyOn(copy, 'trigger');

                copy.configure();

                copy.currentSource = {};
                copy.copying = false;
                copy.startCopyingWorkingCopy();

                expect(copy.trigger.calls.count()).toEqual(1);
                expect(copy.trigger.calls.argsFor(0)).toEqual(['copy:context:change']);
                expect(copy.copying).toBeTruthy();
                expect(copy.currentSource).not.toBeNull();
                expect(copy.currentSource.code).toEqual('working_copy');
            });

            it('updates current source and start copying on a source change event', function () {
                spyOn(copy, 'trigger');

                copy.configure();
                copy.currentSource = {};
                copy.changeCurrentSource('draft');

                expect(copy.trigger.calls.count()).toEqual(1);
                expect(copy.trigger.calls.argsFor(0)).toEqual(['copy:context:change']);
                expect(copy.currentSource).not.toBeNull();
                expect(copy.currentSource.code).toEqual('draft');
            });

            it('updates context on source switcher render event', function () {
                var context = {currentSource: {}, sources: []};

                copy.configure();
                copy.setData({meta: {draft_status: 'status'}});
                copy.ensureSwitcherContext(context);

                expect(context.sources).toContain({
                    'code': 'working_copy',
                    'label': 'pimee_enrich.entity.product.copy.source.working_copy',
                    'type': 'working_copy',
                    'author': null
                });

                expect(context.sources).toContain({
                    'code': 'draft',
                    'label': 'pimee_enrich.entity.product.copy.source.draft',
                    'type': 'my_draft',
                    'author': null
                });

                expect(context.currentSource.code).toBeDefined();
                expect(context.currentSource.code).toEqual('working_copy');
            });

            it('updates context and omit my draft if I am the owner on source switcher render event', function () {
                var context = {currentSource: {}, sources: []};

                copy.configure();
                copy.setData({meta: {draft_status: null}});
                copy.ensureSwitcherContext(context);

                expect(context.sources).toContain({
                    'code': 'working_copy',
                    'label': 'pimee_enrich.entity.product.copy.source.working_copy',
                    'type': 'working_copy',
                    'author': null
                });

                expect(context.sources).not.toContain({
                    'code': 'draft',
                    'label': 'pimee_enrich.entity.product.copy.source.draft',
                    'type': 'my_draft',
                    'author': null
                });

                expect(context.currentSource.code).toBeDefined();
                expect(context.currentSource.code).toEqual('working_copy');
            });

            it('triggers an event on the can be copied method', function () {
                spyOn(copy, 'trigger');

                var field = {attribute: {localizable: false, scopable: false}};

                copy.canBeCopied(field);

                expect(copy.trigger.calls.count()).toEqual(1);
                expect(copy.trigger.calls.argsFor(0)).toEqual([
                    'pim_enrich:form:field:can_be_copied',
                    {
                        field: field,
                        canBeCopied: false,
                        locale: null,
                        scope: null
                    }
                ]);
            });

            it('returns working copy data', function () {
                var workingCopyData = {name: [{locale: null, scope: null, data: 'A name'}]};

                copy.configure();
                copy.currentSource = {type: 'working_copy', author: 'mary'};
                copy.setData({meta: {working_copy: {values: workingCopyData}}});

                var data = copy.getSourceData();

                expect(data).toEqual(workingCopyData);
            });

            it('returns working copy data even if it is empty', function () {
                copy.configure();
                copy.currentSource = {type: 'working_copy', author: 'mary'};
                copy.setData({meta: {working_copy: null}});

                var data = copy.getSourceData();

                expect(data).toEqual({});
            });

            it('returns an other draft data', function () {
                var draftData = {name: [{locale: null, scope: null, data: 'A name'}]};

                copy.configure();
                copy.currentSource = {type: 'draft', author: 'mary'};
                copy.otherDrafts = [{
                    author: 'mary',
                    changes: {
                        values: draftData
                    }
                }];

                var data = copy.getSourceData();

                expect(data).toEqual(draftData);
            });

        });
    }
);
