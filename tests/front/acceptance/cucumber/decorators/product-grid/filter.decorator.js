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

  const setValue = async (operator, value) => {
    await nodeElement.click()

    const valueInput = await nodeElement.$('input')
    await valueInput.type(new String(value), { delay: 800 })

    const operatorDropdown = await nodeElement.$('.operator');
    await operatorDropdown.click()

    const operatorChoice = await getOperatorChoiceByLabel(operator);
    await operatorChoice.click();

    const updateButton = await nodeElement.$('button')
    await updateButton.click();
  }

  return { getName, setValue };
};

module.exports = Filter;
