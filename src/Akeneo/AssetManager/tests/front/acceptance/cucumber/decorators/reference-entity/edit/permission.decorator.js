const Permission = async (nodeElement, createElementDecorator, page) => {
  const setPermission = async (groupName, rightLevel) => {
    const rowSelector = `.AknPermission-row[data-user-group-code="${groupName}"]`;
    const pillSelector = `${rowSelector} .AknPermission-level[data-right-level="${rightLevel}"] .AknPermission-pill`;

    const currentRightLevel = await getRightLevel(groupName);
    if (currentRightLevel !== rightLevel) {
      await page.waitForSelector(pillSelector);

      const pill = await nodeElement.$(pillSelector);
      await page.evaluate(pill => {
        pill.style.width = '20px';
        pill.style.height = '20px';
      }, pill);
      await pill.click();
    }
  };

  const getRightLevel = async groupName => {
    const rowSelector = `.AknPermission-row[data-user-group-code="${groupName}"]`;
    const pillSelector = `${rowSelector} .AknPermission-level .AknPermission-pill.AknPermission-pill--active`;
    await page.waitForSelector(pillSelector);

    const rightLevel = await page.evaluate(pillSelector => {
      const pills = document.querySelectorAll(pillSelector);

      const lastActivePills = pills[pills.length - 1];

      return lastActivePills.parentNode.parentNode.dataset.rightLevel;
    }, pillSelector);

    return rightLevel;
  };

  const isEmpty = async () => {
    const noDataView = await nodeElement.$('.AknGridContainer-noDataImage--user-group');

    return null !== noDataView;
  };

  return {setPermission, getRightLevel, isEmpty};
};

module.exports = Permission;
