const Grid = async (nodeElement, createElementDecorator, page) => {
  const isLoaded = async () => {
    await page.waitFor('.AknGrid');

    return true;
  };

  const hasRow = async identifier => {
    await page.waitForSelector(`a[data-identifier="${identifier}"]`);

    return true;
  };

  const getRecordLabel = async identifier => {
    const label = await nodeElement.$(`a[data-identifier="${identifier}"]`);
    const labelProperty = await label.getProperty('textContent');

    return await labelProperty.jsonValue();
  };

  return {isLoaded, hasRow, getRecordLabel};
};

module.exports = Grid;
