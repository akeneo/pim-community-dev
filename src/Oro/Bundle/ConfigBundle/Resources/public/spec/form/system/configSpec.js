/* global describe, it, expect */
'use strict';

define(
    ['oro/form/system/config', 'pim/form'],
    function (ConfigForm, BaseForm) {
        describe('Config form', function () {
            var configForm = new ConfigForm();
            it('extends base form', function () {
                expect(configForm instanceof BaseForm).toBeTruthy()
            });
        });
    }
);
