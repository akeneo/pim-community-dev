/* global describe, it, expect */
'use strict';

define(
    ['oro/form/system/config/system', 'pim/form', 'pim/form/common/group-selector', 'underscore'],
    function (SystemForm, BaseForm, GroupSelectorForm, _) {
        describe('System form', function () {
            var systemForm = new SystemForm();
            systemForm.code = 'system-form-code';

            it('extends base form', function () {
                expect(systemForm instanceof BaseForm).toBeTruthy()
            });

            it ('configures itself', function () {
                var groupSelectorForm = new GroupSelectorForm();

                spyOn(systemForm, 'trigger');
                spyOn(_, '__').and.returnValue('my_translated_label');
                spyOn(systemForm, 'onExtensions');
                spyOn(systemForm, 'getExtension').and.returnValue(groupSelectorForm);
                spyOn(systemForm, 'getGroups').and.returnValue({});
                spyOn(groupSelectorForm, 'setElements');

                systemForm.configure();

                expect(_.__).toHaveBeenCalledWith('oro_config.form.config.tab.system.title');
                expect(systemForm.onExtensions).toHaveBeenCalledWith('group:change', jasmine.any(Function));
                expect(systemForm.getExtension).toHaveBeenCalledWith('oro-system-config-group-selector');
                expect(systemForm.getGroups).toHaveBeenCalled();
                expect(groupSelectorForm.setElements).toHaveBeenCalledWith({});
                expect(systemForm.trigger).toHaveBeenCalledWith('tab:register', {
                    code: 'system-form-code',
                    label: 'my_translated_label'
                });
            });
        });
    }
);
