const Modal = async (nodeElement, createElementDecorator, page) => {
  const confirmDeletion = async () => {
    // As the button doesn't have any size, we need to make it clickable by giving him a size
    await page.evaluate(modal => {
      const button = modal.querySelector('.AknButtonList-item.ok');

      button.style.width = '100px';
      button.style.height = '100px';
    }, nodeElement);

    const deleteButton = await nodeElement.$('.AknButtonList-item.ok');
    await deleteButton.click();
  };

  const cancelDeletion = async () => {
    // As the button doesn't have any size, we need to make it clickable by giving him a size
    await page.evaluate(modal => {
      const button = modal.querySelector('.AknButtonList-item.cancel');

      button.style.width = '100px';
      button.style.height = '100px';
    }, nodeElement);

    const cancelButton = await nodeElement.$('.AknButtonList-item.cancel');
    await cancelButton.click();
  };

  return {confirmDeletion, cancelDeletion};
};

module.exports = Modal;
