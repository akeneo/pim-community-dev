/* global describe, it, expect */


import CommonSave from 'pim/form/common/save';
import SaveForm from 'oro/form/system/config/save';
import Routing from 'routing';
        describe('Save form', function () {
            var saveForm = new SaveForm({config: {config: {}}});
            it('extends save form', function () {
                expect(saveForm instanceof CommonSave).toBeTruthy()
            });

            it('provides a save url', function () {
                spyOn(Routing, 'generate').and.returnValue('/system/rest');

                expect(saveForm.getSaveUrl()).toBe('/system/rest');
            });

            it('updates the model after save', function () {
                spyOn(saveForm, 'setData');
                var data = {some: 'data'};
                saveForm.postSave(data);

                expect(saveForm.setData).toHaveBeenCalledWith(data);
            });
        });
    
