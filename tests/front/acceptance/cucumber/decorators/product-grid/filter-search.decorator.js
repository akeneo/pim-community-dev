const FilterSearch = async (nodeElement, createElementDecorator, parent) => {

  // const children = {
  //   'Filter':  {
  //     selector: '.AknFilterBox-filterContainer.filter-item',
  //     decorator: Filter,
  //     multiple: true
  //   },
  // };

  const open = async () => {
    const filterButton = await parent.$('.AknFilterBox-addFilterButton')
    await filterButton.click();
  }

  const close = async () => {
    const closeButton = await nodeElement.$('.AknButton--apply')
    await closeButton.click();
  }

  const disableFilters = async (filterNames) => {
    await open();
    await parent.waitForSelector(`label[for="family"]`, { visible: true })

    for (let i = 0; i < filterNames.length; i++) {
      const matchingFilter = await nodeElement.$(`label[for="${filterNames[i]}"]`)
      await matchingFilter.click();
    }

    // const closeButton = await nodeElement.$('.AknButton--apply')
    // await closeButton.click();
  }

  const enableFilter = async (name) => {
    // await open();
    await parent.waitForSelector(`label[for="family"]`, { visible: true })

    const matchingFilter = await parent.waitForSelector(`.filters-column label[for="${name}"]`, { visible: true })
    console.log('name', await (await matchingFilter.getProperty('innerHTML')).jsonValue())

    await matchingFilter.click();
    // const closeButton = await nodeElement.$('.AknButton--apply')
    // await closeButton.click();
  }

  return { open, close, enableFilter, disableFilters };
};

module.exports = FilterSearch;
