'use strict';

define(
  [
    'pim/job-instance-edit-form/save',
    'pim/saver/job-instance-export'
  ],
  function (
    BaseSave,
    JobInstanceSaver
  ) {
    return BaseSave.extend({
      save: function () {
        let jobInstance = $.extend(true, {}, this.getFormData());

        delete jobInstance.meta;
        delete jobInstance.connector;

        this.showLoadingMask();
        this.getRoot().trigger('pim_enrich:form:entity:pre_save');

        return this.getJobInstanceSaver()
          .save(jobInstance.code, jobInstance)
          .then(function (data) {
            this.postSave();

            this.setData(data);
            this.getRoot().trigger('pim_enrich:form:entity:post_fetch', data);
          }.bind(this))
          .fail(this.fail.bind(this))
          .always(this.hideLoadingMask.bind(this));
      },
      getJobInstanceSaver: function () {
        return JobInstanceSaver;
      }
    });
  }
);
