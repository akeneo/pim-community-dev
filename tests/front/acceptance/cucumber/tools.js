const random = process.env.RANDOM_LATENCY !== 'false';
const maxRandomLatency = undefined !== process.env.MAX_RANDOM_LATENCY_MS ? process.env.MAX_RANDOM_LATENCY_MS : 1000;

const answer = (methodToDelay, randomLatency = random, customMaxRandomLatency = maxRandomLatency) => {
  setTimeout(methodToDelay, (randomLatency ? Math.random() : 0.1) * parseInt(customMaxRandomLatency));
};

const answerJson = (
  request,
  response,
  status = 200,
  randomLatency = random,
  customMaxRandomLatency = maxRandomLatency
) => {
  answer(() => request.respond(json(response, status)), randomLatency, parseInt(customMaxRandomLatency));
};

const json = (body, status) => ({
  status: status,
  contentType: 'application/json',
  body: typeof body === 'string' ? body : JSON.stringify(body),
});

const csvToArray = (csv, separator = ',') => {
  return csv.split(separator).map(value => value.trim());
};

const convertDataTable = dataTable => {
  return dataTable.rawTable.reduce((result, current) => {
    try {
      result[current[0]] = JSON.parse(current[1]);
    } catch (e) {
      result[current[0]] = current[1];
    }

    return result;
  }, {});
};

const convertItemTable = dataTable => {
  const [keys, ...items] = dataTable.rawTable;

  return items.map(values => {
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
  return await page.evaluate(
    (viewData, extension) => {
      const FormBuilder = require('pim/form-builder');

      return FormBuilder.build(extension).then(async form => {
        form.setData(viewData);
        form.setElement(document.getElementById('app')).render();

        return form;
      });
    },
    data,
    extension
  );
};

module.exports = {
  answer,
  answerJson,
  json,
  csvToArray,
  renderView,
  convertDataTable,
  convertItemTable,
};
