/* global define, describe, it, expect */
define(['oro/mediator', 'backbone'],
function(mediator, Backbone) {
    'use strict';

    describe('oro/mediator', function () {
        it("compare mediator to Backbone.Events", function() {
            expect(mediator).toEqual(Backbone.Events);
            expect(mediator).not.toBe(Backbone.Events);
        });
    });
});
