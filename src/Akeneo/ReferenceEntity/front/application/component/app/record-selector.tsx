import * as React from 'react';
import * as ReactDOM from 'react-dom';
import * as $ from 'jquery';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
const routing = require('routing');
import recordFetcher from 'akeneoreferenceentity/infrastructure/fetcher/record';
import {Query} from 'akeneoreferenceentity/domain/fetcher/fetcher';

export interface Select2Props {
  value: RecordCode[] | RecordCode;
  referenceEntityIdentifier: ReferenceEntityIdentifier;
  multiple: boolean;
  onChange: (value: RecordCode[] | RecordCode) => void;
}

// interface Filter {
//   field: string;
//   operator: string;
//   value: any;
//   context: any;
// }

// export interface Query {
//   locale: string;
//   size: number;
//   channel: string;
//   page: number;
//   filters: Filter[];
// }

export default class RecordSelector extends React.Component<Select2Props> {
  public props: any;
  private el: any;

  componentDidMount() {
    this.el = $(ReactDOM.findDOMNode(this) as Element);

    if (undefined !== this.el.val(this.props.value).select2) {
      this.el.val(this.props.value).select2({
        allowClear: true,
        ajax: {
            url: routing.generate('akeneo_reference_entities_record_index_rest', {
              referenceEntityIdentifier: this.props.referenceEntityIdentifier.stringValue(),
            }),
            quietMillis: 250,
            cache: true,
            transport: (params: any, success: (results: any) => void, failure: () => void) => {
              const promise = $.Deferred();

              const records = recordFetcher.search(params.data).then((result: any) => {
                debugger
                promise.resolve(records);
              }).catch(() => {
                promise.reject();
              });

              return promise.promise();
            },
            data: (term: string, page: number): Query => {
                return {
                  locale: 'en_US',
                  size: 200,
                  channel: 'ecommerce',
                  page,
                  filters: [
                    {
                      field: 'reference_entity',
                      operator: '=',
                      value: this.props.referenceEntityIdentifier.stringValue()
                    }
                    {
                      field: 'search',
                      operator: '=',
                      value: term
                    }
                  ]
                };
            },
            results: function (records: any) {
                // var data = {
                //     more: 20 === _.keys(families).length,
                //     results: []
                // };
                // _.each(families, function (value, key) {
                //     data.results.push({
                //         id: key,
                //         text: i18n.getLabel(value.labels, UserContext.get('uiLocale'), value.code)
                //     });
                // });

                // return data;
            }
        },
        initSelection: function (element, callback) {
            // if (null !== initialValue) {
            //     FetcherRegistry.getFetcher('family')
            //         .fetch(initialValue)
            //         .then(function (family) {
            //             callback({
            //                 id: family.code,
            //                 text: i18n.getLabel(
            //                     family.labels,
            //                     UserContext.get('uiLocale'),
            //                     family.code
            //                 )
            //             });
            //         });
            // }
        }
    });
      this.el.on('change', (event: any) => {
        this.props.onChange(event.val);
      });
    }
  }

  componentWillUnmount() {
    this.el.off('change');
  }

  render(): JSX.Element | JSX.Element[] {
    // const {data, value, ...props} = this.props;

    return (
      <input type="hidden"/>
    );
  }
}
