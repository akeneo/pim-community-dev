/* global describe, it, expect, spyOn */
'use strict';

define(
    ['oro/multiselect-decorator', 'jquery', 'underscore', 'routing'],
    function (MultiSelectDecorator, $, _, Routing) {
        describe('Decorate the MultiSelect element', function () {

            it('computes the minimum dropdown width', function () {
                var firstElement = 'first';
                var secondElement = 'second';

                var firstSelector = jasmine.createSpyObj('selector 1', ['find']);
                var secondSelector = jasmine.createSpyObj('selector 2', ['find']);

                firstSelector.find.and.callFake(function () {
                    var dom = jasmine.createSpyObj('dom 1', ['html', 'width']);
                    dom.html.and.returnValue('The first attribute');
                    dom.width.and.returnValue(150);

                    return dom;
                });

                secondSelector.find.and.callFake(function () {
                    var dom = jasmine.createSpyObj('dom 2', ['html', 'width']);
                    dom.html.and.returnValue('The second attribute');
                    dom.width.and.returnValue(175);

                    return dom;
                });

                var jqueryResponse = {
                    first: firstSelector,
                    second: secondSelector
                };

                spyOn($.fn, 'init').and.callFake(function (param) {
                    return jqueryResponse[param];
                });

                var widget = jasmine.createSpyObj('widget', ['find']);
                widget.find.and.returnValue([firstElement, secondElement]);

                spyOn(MultiSelectDecorator.prototype, 'initialize').and.returnValue(null);
                spyOn(MultiSelectDecorator.prototype, 'getWidget').and.returnValue(widget);

                var multiSelectDecorator = new MultiSelectDecorator();
                var miniWidth = multiSelectDecorator.getMinimumDropdownWidth();

                expect(miniWidth).toEqual(201)
            });
        });
    }
);
