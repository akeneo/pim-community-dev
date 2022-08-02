const BaseSave = require('pimui/js/job/common/edit/save');
const jobInstanceSaver = require('pim/saver/job-instance-import');

class Save extends BaseSave {
  save() {
    const jobInstance = {...this.getFormData()};

    delete jobInstance.meta;
    delete jobInstance.connector;

    this.showLoadingMask();
    this.getRoot().trigger('pim_enrich:form:entity:pre_save');

    return jobInstanceSaver
      .save(jobInstance.code, jobInstance)
      .then((data: any) => {
        this.postSave();

        this.setData(data);
        this.getRoot().trigger('pim_enrich:form:entity:post_fetch', data);
      })
      .fail(this.fail.bind(this))
      .always(this.hideLoadingMask.bind(this));
  }
}

export = Save;
