/* global describe, it, expect */
'use strict';

define(
    ['pim/i18n'],
    function (i18n) {
        describe('Internationalization module', function () {

            it('expose a label generator', function () {
                expect(i18n.getLabel).toBeDefined();
            });

            it('generate a label for the given locale', function () {
                expect(i18n.getLabel(
                    {'en_US': 'My label', 'fr_FR': 'Mon libellé'},
                    'en_US',
                    'my_code'
                )).toBe('My label');
            });

            it('generate a fallback if the label on the given locale does not exists', function () {
                expect(i18n.getLabel(
                    {'en_US': 'My label', 'fr_FR': 'Mon libellé'},
                    'de_DE',
                    'my_code'
                )).toBe('[my_code]');
            });
        });
    }
);
