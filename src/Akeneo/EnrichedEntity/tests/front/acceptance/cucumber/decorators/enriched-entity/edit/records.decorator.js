const Records = async (nodeElement, createElementDecorator, page) => {
  const isLoaded = async () => {
    return true;
  };

  const hasRecord = async (code, type) => {
    await isLoaded();
    await page.waitFor('.AknDefault-mainContent .AknGrid-bodyRow');
    const record = await nodeElement.$(`.AknGrid-bodyCell > a[data-identifier="${code}"]`);

    return record !== null;
  };

  const isEmpty = async () => {
    await page.waitFor('.AknDefault-mainContent .AknGridContainer-noData');

    return true;
  };

  return {hasRecord, isLoaded, isEmpty};
};

module.exports = Records;
