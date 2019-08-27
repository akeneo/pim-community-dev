const timeout = (ms) => {
  return new Promise(resolve => setTimeout(resolve, ms));
}

const Filter = async (nodeElement) => {
  const getName = async () => {
    const name = await nodeElement.$('.AknFilterBox-filterLabel')
    const text = await (await name.getProperty('textContent')).jsonValue();

    return text.trim();
  };

  const open = async (operator, value) => {
    return nodeElement.click()
    // await timeout(1000);
    // // nodeElement is out of date
    // console.log((await nodeElement.getProperty('innerHTML')).jsonValue())
    // // await nodeElement.waitForSelector('.operator', { visible: true })
    // const operatorDropdown = await nodeElement.$('.operator')
    // console.log(operatorDropdown)
    // return true;
  }

  const setValue = async (operator, value) => {
     console.log((await nodeElement.getProperty('innerHTML')).jsonValue())
  }

  return { getName, open, setValue };
};

module.exports = Filter;
