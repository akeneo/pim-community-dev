const Filter = async (nodeElement) => {
  const getName = async () => {
    const name = await nodeElement.$('.AknFilterBox-filterLabel')
    const text = await (await name.getProperty('textContent')).jsonValue();

    return text.trim();
  };

  const getOperatorChoiceByLabel = async (choiceLabel) => {
    const operatorChoices = await nodeElement.$$('.operator_choice');
    let matchingChoice = null;

    for(let i = 0; i < operatorChoices.length; i++) {
      const text = await (await operatorChoices[i].getProperty('textContent')).jsonValue();
      if (text.trim() === choiceLabel) {
        matchingChoice = operatorChoices[i];
        break;
      }
    }

    return matchingChoice;
  }

  const remove = async () => {
    const closeButton = await nodeElement.$('.AknFilterBox-disableFilter')
    await closeButton.click()
  }

  const setValue = async (operator, value) => {
    try {
    await nodeElement.click()

    const valueInput = await nodeElement.$('input')

    if (null !== valueInput) {
      await valueInput.type(new String(value))
    }

    const operatorDropdown = await nodeElement.$('.operator');
    await operatorDropdown.click()

    const operatorChoice = await getOperatorChoiceByLabel(operator);
    await operatorChoice.click();

    const updateButton = await nodeElement.$('button')
    await updateButton.click();

    } catch (e) {
      console.log(`Could not find operator by label ${operator}`)
    }
  }

  return { getName, setValue, remove };
};

module.exports = Filter;
