const routing = require('routing');
import * as jQuery from 'jquery';
import { RawProductInterface } from 'pimfront/product/domain/model/product';

interface Filter {
  field: string;
  operator: string;
  value: any;
  options: Array<any>
}

export interface SearchOptions {
  filters: Filter[];
  locale: string;
  scope: string;
  limit: number;
  from: number;
}

const queryToParameters = (query: any) => {
  return {
    default_locale: query.locale,
    default_scope: query.channel,
    limit: query.limit,
    from: query.page * query.limit
  }
}

class ProductGridFetcher {
  private options: {
    urls: {
      list: string;
    };
  };

  constructor (options: {urls: {list: string}}) {
    this.options = options;
  }

  search (query: SearchOptions): Promise<RawProductInterface[]> {
    return new Promise((resolve, reject) => {
      jQuery.ajax({url: routing.generate(this.options.urls.list), data: queryToParameters(query)})
        .then((products: RawProductInterface[]) => {
            resolve(products);
        })
        .fail(([...args]) => {
          reject.apply(args);
        });
    });
  }

  clear () {

  }
};

export default ProductGridFetcher;

