import BaseView from 'pimui/js/view/base';
import {Meta, MetaProps} from '@akeneo-pim-enterprise/tailored-import';

class MetaView extends BaseView {
  public config: any;

  constructor(options: {config: any}) {
    super(options);

    this.config = {...this.config, ...options.config};
  }

  configure() {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render.bind(this));

    return BaseView.prototype.configure.apply(this);
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    const formData = this.getFormData();

    const props: MetaProps = {
      jobName: formData.job_name,
      connector: formData.connector,
      importStructure: {
        columns: formData.configuration.import_structure.columns ?? [],
        data_mappings: formData.configuration.import_structure.data_mappings ?? [],
      },
    };

    this.renderReact<MetaProps>(Meta, props, this.el);

    return this;
  }
}

export = MetaView;
