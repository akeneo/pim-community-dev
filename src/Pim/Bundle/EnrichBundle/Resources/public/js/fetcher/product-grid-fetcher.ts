const routing = require('routing');

interface Filter {
  field: string;
  operator: string;
  value: any;
  options: Array<any>
}

interface SearchOptions {
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

  search (searchOptions: SearchOptions): Promise<any> {
    return new Promise((resolve, reject) => {
        let xhr = new XMLHttpRequest();
        xhr.open('POST', routing.generate(this.options.urls.list));
        xhr.setRequestHeader('Content-Type', 'application/json');

        xhr.onload = () => {
            if (xhr.status >= 200 && xhr.status < 300) {
                resolve(JSON.parse(xhr.response));
            } else {
                reject(xhr.statusText);
            }
        };
        xhr.onerror = () => reject(xhr.statusText);
        xhr.send(JSON.stringify(searchOptions));
    });
  }
};

export default ProductGridFetcher;
