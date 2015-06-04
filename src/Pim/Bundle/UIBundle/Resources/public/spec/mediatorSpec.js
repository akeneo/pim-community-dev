/* global describe, it, expect */
'use strict';

define(
    ['oro/mediator', 'backbone'],
    function (mediator, Backbone) {
        describe('mediator', function () {
            it('extends Backbone.Events', function () {
                expect(mediator).toEqual(Backbone.Events);
            });
        });
    }
);
