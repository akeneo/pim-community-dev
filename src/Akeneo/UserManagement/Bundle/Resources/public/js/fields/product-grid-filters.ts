import * as $ from 'jquery';
import * as i18n from 'pimenrich/js/i18n';
const BaseMultiSelectAsync = require('pim/form/common/fields/multi-select-async');
const UserContext = require('pim/user-context');

class ProductGridFilters extends BaseMultiSelectAsync {
  /**
   * {@inheritdoc}
   *
   * Removes the useless catalogLocale field, and add grid filter
   */
  select2Data(term: string, page: number) {
    return {
      // TODO Adds the product grid filters
      search: term,
      options: {
        limit: this.resultsPerPage,
        page: page
      }
    };
  }

  /**
   * {@inheritdoc}
   * TODO Reput real signature
   */
  convertBackendItem(item: any) {
    return {
      id: item.code,
      text: i18n.getLabel(item.labels, UserContext.get('catalogLocale'), item.code),
      group: {
        text: 'toto'
        // (
        //   item.group ?
        //     i18n.getLabel(
        //       this.attributeGroups[item.group].labels,
        //       UserContext.get('catalogLocale'),
        //       this.attributeGroups[item.group]
        //     ) : ''
        // )
      }
    };
  }

  /**
   * {@inheritdoc}
   */
  select2InitSelection(element: any, callback: any) {
    const strValues:string = (<any> $(element)).val();
    const values = strValues.split(',');
    if (values.length > 0) {
      $.ajax({
        url: this.choiceUrl,
        data: { identifiers: strValues },
        type: this.choiceVerb
      }).then(response => {
        let selecteds = Object.values(response.results).filter((item: { code: string }) => {
          return values.indexOf(item.code) > -1;
        });

        selecteds = selecteds.map((selected) => {
          return this.convertBackendItem(selected);
        });

        callback(selecteds);
      });
    }
  }
}

export = ProductGridFilters
