/* global describe, it, expect */
'use strict';

define(
    ['pim/form/common/save', 'oro/form/system/config/save', 'routing'],
    function (CommonSave, SaveForm, Routing) {
        describe('Save form', function () {
            var saveForm = new SaveForm({config: {config: {}}});
            it('extends save form', function () {
                expect(saveForm instanceof CommonSave).toBeTruthy()
            });

            it('provides a save url', function () {
                spyOn(Routing, 'generate').and.returnValue('/system/rest');

                expect(saveForm.getSaveUrl()).toBe('/system/rest');
                expect(Routing.generate).toHaveBeenCalledWith('oro_config_configuration_system_post');
            });

            it('updates the model after save', function () {
                spyOn(saveForm, 'setData');
                var data = {some: 'data'};
                saveForm.postSave(data);

                expect(saveForm.setData).toHaveBeenCalledWith(data);
            });
        });
    }
);
