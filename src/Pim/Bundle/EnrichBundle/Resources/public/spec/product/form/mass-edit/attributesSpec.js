/* global describe, it, expect, spyOn, beforeEach */
'use strict';

define(
    [
        'jquery',
        'pim/mass-product-edit-form/attributes',
        'pim/security-context'
    ],
    function (
        $,
        Attributes,
        SecurityContext
    ) {
        describe('Mass edit attributes module', function () {
            var event = {
                currentTarget: {
                    dataset: {
                        attribute: 'description'
                    }
                }
            };

            var product;
            var attributes;

            beforeEach(function () {
                product = {
                    values: {
                        name: [
                            {
                                data: 'The name',
                                locale: null,
                                scope: null
                            }
                        ],
                        description: [
                            {
                                data: 'El description',
                                locale: 'es_ES',
                                scope: null
                            },
                            {
                                data: 'La description',
                                locale: 'fr_FR',
                                scope: null
                            }
                        ]
                    }
                };

                attributes = new Attributes();

                spyOn(SecurityContext, 'isGranted').and.returnValue(true);
                spyOn(attributes, 'getFormData').and.returnValue(product);
                spyOn(attributes, 'setData');
                spyOn($, 'ajax');

                attributes.removeAttribute(event);
            });

            it('does not make an XHR call', function () {
                expect($.ajax).not.toHaveBeenCalled();
            });

            it('removes the attribute from the product', function () {
                var expectedProduct = {
                    values: {
                        name: [
                            {
                                data: 'The name',
                                locale: null,
                                scope: null
                            }
                        ]
                    }
                };

                expect(attributes.setData).toHaveBeenCalledWith(expectedProduct);
            });
        });
    }
);
