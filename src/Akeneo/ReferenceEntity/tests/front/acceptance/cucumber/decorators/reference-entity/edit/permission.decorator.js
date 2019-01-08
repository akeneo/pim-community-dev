const Permission = async (nodeElement, createElementDecorator, page) => {
  const setPermission = async (groupName, rightLevel) => {
    const pillSelector = `.AknPermission-row[data-user-group-code="${groupName}"] .AknPermission-level[data-right-level="${rightLevel}"] .AknPermission-pill`;

    await page.waitForSelector(pillSelector);
    const pill = await nodeElement.$(pillSelector);
    await pill.click();
  };

  const getRightLevel = async groupName => {
    const pillSelector = `.AknPermission-row[data-user-group-code="${groupName}"] .AknPermission-level .AknPermission-pill.AknPermission-pill--active`;

    const rightLevel = await page.evaluate(pillSelector => {
      const pills = document.querySelectorAll(pillSelector);

      const lastActivePills = pills[pills.length - 1];

      return lastActivePills
        .getParent()
        .getParent()
        .getParent().dataset.rightLevel;
    }, pillSelector);

    return rightLevel;
  };

  return {setPermission, getRightLevel};
};

module.exports = Permission;
