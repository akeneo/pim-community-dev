/* global define, require, describe, it, expect, beforeEach, afterEach, spyOn, jasmine */
define(['oro/sync', 'requirejs-exposure'],
function (sync, requirejsExposure) {
    'use strict';

    var exposure = requirejsExposure.disclose('oro/sync');

    describe('oro/sync', function () {
        var service, messenger, __;
        exposure.backup('service');

        beforeEach(function () {
            service = jasmine.createSpyObj('service', ['subscribe', 'unsubscribe', 'connect']);
            service.on = jasmine.createSpy('service.on').andReturn(service);
            service.once = jasmine.createSpy('service.once').andReturn(service);
            service.off = jasmine.createSpy('service.off').andReturn(service);

            messenger = jasmine.createSpyObj('messenger', ['notificationMessage', 'notificationFlashMessage']);

            exposure.substitute('__').by(__ = jasmine.createSpy('__'));
            exposure.substitute('messenger').by(messenger);
        });

        afterEach(function () {
            exposure.recover('__');
            exposure.recover('messenger');
            exposure.recover('service');
        });

        it('setup service', function () {
            expect(function () {
                sync({});
            }).toThrow();
            expect(exposure.retrieve('service')).toBeUndefined();
            expect(function () {
                sync(service);
            }).not.toThrow();
            expect(exposure.retrieve('service')).toBe(service);
        });

        it('setup connection_lost handler', function () {
            sync(service);
            expect(service.on).toHaveBeenCalledWith('connection_lost', jasmine.any(Function));
        });

        describe('handle connection_lost event', function () {
            var connectionLostHandler;
            beforeEach(function () {
                sync(service);
                connectionLostHandler = service.on.mostRecentCall.args[1];
            });

            it('show message', function () {
                connectionLostHandler();
                expect(messenger.notificationMessage).toHaveBeenCalled();
                expect(messenger.notificationMessage.mostRecentCall.args[2]).toEqual({flash: false});
                connectionLostHandler({retries: 1});
                expect(messenger.notificationMessage.mostRecentCall.args[2]).toEqual({flash: true});
            });

            it('setup connection_established handler', function () {
                connectionLostHandler();
                expect(service.off).toHaveBeenCalledWith('connection_established', jasmine.any(Function));
                expect(service.once).toHaveBeenCalledWith('connection_established', jasmine.any(Function));
                expect(service.once.mostRecentCall.args[1]).toEqual(service.off.mostRecentCall.args[1]);
                // check connection established handler
                (service.once.mostRecentCall.args[1])();
                expect(messenger.notificationFlashMessage).toHaveBeenCalled();
            });
        });

        it('check private method checkService', function () {
            var checkService = exposure.retrieve('checkService');
            exposure.substitute('service').by(undefined);
            expect(checkService).toThrow();
            exposure.substitute('service').by({});
            expect(checkService).not.toThrow();
        });

        describe('model changes subscription', function () {
            var model,
                subscribeModel = exposure.retrieve('subscribeModel'),
                unsubscribeModel = exposure.retrieve('unsubscribeModel');

            beforeEach(function () {
                exposure.substitute('service').by(service);
                model = jasmine.createSpyObj('model', ['set']);
                model.on = jasmine.createSpy('model.on').andReturn(model);
                model.url = jasmine.createSpy('model.url').andReturn('some/model/1');
            });

            it('subscribe new model', function () {
                subscribeModel(model);
                expect(service.subscribe).not.toHaveBeenCalled();
            });

            it('subscribe existing model', function () {
                var setModelAttrsCallback;
                model.id = 1;
                subscribeModel(model);
                expect(service.subscribe).toHaveBeenCalledWith(model.url(), jasmine.any(Function));
                expect(model.on).toHaveBeenCalledWith('remove', unsubscribeModel);
                // same callback function event
                setModelAttrsCallback = service.subscribe.mostRecentCall.args[1];
                subscribeModel(model);
                expect(service.subscribe.mostRecentCall.args[1]).toBe(setModelAttrsCallback);
            });

            it('unsubscribe new model', function () {
                subscribeModel(model);
                unsubscribeModel(model);
                expect(service.unsubscribe).not.toHaveBeenCalled();
            });

            it('unsubscribe existing model', function () {
                var setModelAttrsCallback;
                model.id = 1;
                subscribeModel(model);
                setModelAttrsCallback = service.subscribe.mostRecentCall.args[1];
                unsubscribeModel(model);
                expect(service.unsubscribe).toHaveBeenCalledWith(model.url(), setModelAttrsCallback);
            });
        });

        describe("check sync's methods", function () {
            beforeEach(function () {
                exposure.substitute('service').by(service);
            });

            describe('tracking changes', function () {
                var obj, subscribeModel, unsubscribeModel,
                    Backbone = {
                        Model: function () {},
                        Collection: function () {
                            this.on = jasmine.createSpy('collection.on');
                            this.off = jasmine.createSpy('collection.off');
                        }
                    };
                beforeEach(function () {
                    exposure.substitute('subscribeModel')
                        .by(subscribeModel = jasmine.createSpy('subscribeModel'));
                    exposure.substitute('unsubscribeModel')
                        .by(unsubscribeModel = jasmine.createSpy('subscribeModel'));
                    exposure.substitute('Backbone').by(Backbone);
                });
                afterEach(function () {
                    exposure.recover('subscribeModel');
                    exposure.recover('unsubscribeModel');
                    exposure.recover('Backbone');
                });

                it('of any object', function () {
                    obj = {};
                    sync.keepRelevant(obj);
                    expect(subscribeModel).not.toHaveBeenCalled();
                    sync.stopTracking(obj);
                    expect(unsubscribeModel).not.toHaveBeenCalled();
                });

                it('of Backbone.Model', function () {
                    obj = new Backbone.Model();
                    sync.keepRelevant(obj);
                    expect(subscribeModel.callCount).toEqual(1);
                    sync.stopTracking(obj);
                    expect(unsubscribeModel.callCount).toEqual(1);
                });

                describe('of Backbone.Collection', function () {
                    beforeEach(function () {
                        obj = new Backbone.Collection();
                        obj.url = 'some/model';
                        obj.models = [new Backbone.Model(), new Backbone.Model(), new Backbone.Model()];
                    });

                    it('tracking collection changes', function () {
                        sync.keepRelevant(obj);
                        expect(subscribeModel.callCount).toEqual(obj.models.length);
                        expect(obj.on).toHaveBeenCalled();
                        sync.stopTracking(obj);
                        expect(unsubscribeModel.callCount).toEqual(obj.models.length);
                        expect(obj.off).toHaveBeenCalledWith(obj.on.mostRecentCall.args[0]);
                    });

                    describe('consistency of handling events', function () {
                        var events;
                        beforeEach(function () {
                            sync.keepRelevant(obj);
                            events = obj.on.mostRecentCall.args[0];
                        });

                        it('collection "add" event', function () {
                            expect(events.add).toEqual(exposure.original('subscribeModel'));
                        });

                        it('collection "error" event', function () {
                            expect(events.error).toEqual(jasmine.any(Function));
                            events.error(obj);
                            // remove subscription for each models
                            expect(unsubscribeModel.callCount).toEqual(obj.models.length);
                        });

                        it('collection "reset" event', function () {
                            var options = {previousModels: obj.models};
                            obj.models = [new Backbone.Model(), new Backbone.Model()];
                            subscribeModel.reset();
                            expect(events.reset).toEqual(jasmine.any(Function));
                            events.reset(obj, options);
                            // remove subscription for each previous models
                            expect(unsubscribeModel.callCount).toEqual(options.previousModels.length);
                            // add subscription for each new models
                            expect(subscribeModel.callCount).toEqual(obj.models.length);
                        });
                    });
                });
            });

            it('sync.reconnect', function () {
                sync.reconnect();
                expect(service.connect).toHaveBeenCalled();
            });

            it('sync.getService', function () {
                expect(sync.getService()).toBe(service);
            });
        });
    });
});
