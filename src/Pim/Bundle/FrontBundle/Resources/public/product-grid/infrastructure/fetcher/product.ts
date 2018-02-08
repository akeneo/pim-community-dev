import * as routing from 'routing';
import * as jQuery from 'jquery';
import to from 'await-to-js';
import {RawProductInterface} from 'pimfront/product/domain/model/product';

interface Filter {
  field: string;
  operator: string;
  value: any;
  options: Array<any>;
}

export interface SearchOptions {
  filters: Filter[];
  locale: string;
  scope: string;
  limit: number;
  from: number;
}

export interface ServerResponse {
  items: RawProductInterface[];
  total: number;
}

const queryToParameters = (query: any) => {
  return {
    default_locale: query.locale,
    default_scope: query.channel,
    limit: query.limit,
    from: query.page * query.limit,
    filters: query.filters,
  };
};

class ProductGridFetcher {
  private options: {
    urls: {
      list: string;
    };
  };

  constructor(options: {urls: {list: string}}) {
    this.options = options;
  }

  search(query: SearchOptions): Promise<[any, ServerResponse | undefined]> {
    return to<ServerResponse>(
      new Promise((resolve, reject) => {
        jQuery
          .ajax({
            url: routing.generate(this.options.urls.list),
            data: queryToParameters(query),
          })
          .then((response: ServerResponse) => resolve(response))
          .fail((error: any) => reject({status: error.status, text: error.responseText}));
      })
    );
  }

  clear() {}
}

export default ProductGridFetcher;
