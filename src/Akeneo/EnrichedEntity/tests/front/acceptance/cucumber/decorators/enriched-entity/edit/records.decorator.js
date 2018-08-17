const Records = async (nodeElement, createElementDecorator, page) => {
  const isLoaded = async () => {
    return true;
  };

  const hasRecord = async (code) => {
    await isLoaded();
    await page.waitFor('.AknDefault-mainContent .AknGrid-bodyRow');
    const record = await nodeElement.$(`.AknGrid-bodyCell > a[data-identifier="${code}"]`);

    return record !== null;
  };

  const isEmpty = async () => {
    await isLoaded();
    await page.waitFor('.AknDefault-mainContent .AknGridContainer-noData');

    return true;
  };

  const getRecordLabel = async identifier => {
    const label = await nodeElement.$(`a[data-identifier="${identifier}"]`);
    const labelProperty = await label.getProperty('textContent');

    return await labelProperty.jsonValue();
  };

  return {hasRecord, isLoaded, isEmpty, getRecordLabel};
};

module.exports = Records;
