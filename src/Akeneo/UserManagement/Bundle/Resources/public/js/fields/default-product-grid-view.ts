import * as _ from 'underscore';
const BaseSelect = require('pim/form/common/fields/simple-select-async');
const Routing = require('routing');

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
type InterfaceNormalizedDatagridView = {
  id: string;
  label: string;
  owner_id: number;
  type: 'public'|'project';
  datagrid_alias: string;
}

class DefaultProductGridView extends BaseSelect {
  private config: any;

  constructor(options: {config: any}) {
    super(options);

    this.config = {...this.config, ...options.config};
  }

  /**
   * {@inheritdoc}
   */
  configure() {
    return $.when(
      BaseSelect.prototype.configure.apply(this, arguments)
    ).then(() => {
      this.setChoiceUrl(Routing.generate(this.config.choiceUrl, {alias: this.config.datagridAlias}));
      this.allowClear = true;
    });
  }

  /**
   * {@inheritdoc}
   */
  convertBackendItem(item: InterfaceNormalizedDatagridView) {
    return {
      id: parseInt(item.id),
      text: item.label,
    };
  }

  /**
   * {@inheritdoc}
   */
  select2InitSelection(element: any, callback: any) {
    const id: string = <string> $(element).val();
    if ('' !== id) {
      $.ajax({
        url: this.choiceUrl,
        data: {options: {identifiers: [id]}},
        type: this.choiceVerb,
      }).then(response => {
        const selected: InterfaceNormalizedDatagridView|undefined = _.findWhere(response, {id: parseInt(id)});
        if (undefined !== selected) {
          callback(this.convertBackendItem(selected));
        }
      });
    }
  }
}

export = DefaultProductGridView;
