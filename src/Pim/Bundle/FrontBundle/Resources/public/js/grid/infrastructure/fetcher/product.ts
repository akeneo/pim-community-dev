const routing = require('routing');
import * as jQuery from 'jquery';
import { RawProductInterface } from 'pimfront/js/product/domain/model/product';

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

class ProductGridFetcher {
  private options: {
    urls: {
      list: string;
    };
  };

  constructor (options: {urls: {list: string}}) {
    this.options = options;
  }

  search (searchOptions: SearchOptions): Promise<RawProductInterface[]> {
    return new Promise((resolve, reject) => {
      jQuery.ajax({url: routing.generate(this.options.urls.list), data: searchOptions})
        .then((products: RawProductInterface[]) => {
            resolve(products);
        })
        .fail(([...args]) => {
          reject.apply(args);
        });
    });
  }
};

export default ProductGridFetcher;

