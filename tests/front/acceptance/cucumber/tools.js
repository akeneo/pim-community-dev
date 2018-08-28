const random = process.env.RANDOM_LATENCY || true;
const maxRandomLatency = undefined !== process.env.MAX_RANDOM_LATENCY_MS ? process.env.MAX_RANDOM_LATENCY_MS : 1000;
let pageListenerCollection = null;

class PageListenerCollection {
  constructor(page) {
    this.page = page;
    this.listeners = [];
  }

  add(url, method, response, status, randomLatency, customMaxRandomLatency) {
    this.remove(url, method);
    const listener = {
      url,
      method,
      response,
      status,
      randomLatency,
      customMaxRandomLatency
    };

    this.listeners.push(listener);
  }

  find(url, method) {
    return this.listeners.find((listener) => {
      return listener.url === url && listener.method === method;
    });
  }

  rebindAll() {
    this.page.removeAllListeners('request');

    const urls = [];
    this.listeners.forEach((listener) => {
      urls.push(listener.method + '|' + listener.url);
      this.page.on('request', request => {
        if (request.url() === listener.url && request.method() === listener.method) {
          answerJson(request, listener.response, listener.status);
        }
      });
    });

    this.page.on('request', request => {
      if (!urls.includes(request.method() + '|' + request.url())) {
        answerJson(request, {}, 200);
      }
    });
  }

  remove(url, method) {
    this.listeners = this.listeners.filter(listener => listener.url !== url || listener.method !== method);
  }
}

const answer = (methodToDelay, randomLatency = random, customMaxRandomLatency = maxRandomLatency) => {
  setTimeout(methodToDelay, (randomLatency ? Math.random() : 1) * parseInt(customMaxRandomLatency));
};

const answerJson = (request, response, status = 200, randomLatency = random, customMaxRandomLatency = maxRandomLatency) => {
  answer(() => request.respond(json(response, status)), randomLatency, parseInt(customMaxRandomLatency));
};

const json = (body, status) => ({
  status: status,
  contentType: 'application/json',
  body: typeof body === 'string' ? body : JSON.stringify(body)
});

const csvToArray = (csv, separator = ',') => {
  return csv.split(separator).map(value => value.trim());
};

const convertDataTable = (dataTable) => {
  return dataTable.rawTable.reduce((result, current) => {
    try {
      result[current[0]] = JSON.parse(current[1]);
    } catch (e) {
      result[current[0]] = current[1];
    }

    return result;
  }, {});
};

const convertItemTable = (dataTable) => {
  const [keys, ...items] = dataTable.rawTable;

  return items.map((values) => {
    return values.reduce((result, value, key) => {
      try {
        result[keys[key]] = JSON.parse(value);
      } catch (e) {
        result[keys[key]] = value;
      }

      return result;
    }, {});
  });
};

const renderView = async (page, extension, data) => {
  return await page.evaluate((volumes) => {
    const FormBuilder = require('pim/form-builder');

    return FormBuilder.build('pim-catalog-volume-index').then((form) => {
      form.setData(volumes);
      form.setElement(document.getElementById('app')).render();

      return form;
    });
  }, data);
};

const addRequestListener = (page, url, method, response, status = 200, randomLatency = random, customMaxRandomLatency = maxRandomLatency) => {
  if (null === pageListenerCollection) {
    pageListenerCollection = new PageListenerCollection(page);
  }

  pageListenerCollection.add(url, method, response, status, randomLatency, customMaxRandomLatency);
  pageListenerCollection.rebindAll();
};

module.exports = {
  answer,
  answerJson,
  json,
  csvToArray,
  renderView,
  convertDataTable,
  convertItemTable,
  addRequestListener
};
