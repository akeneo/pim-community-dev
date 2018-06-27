const Breadcrumb = async (nodeElement, createElementDecorator, page) => {
  const clickOnItem = async () => {
    const breadcrumbItem = await nodeElement.$('.AknBreadcrumb-item');
    await breadcrumbItem.click();
  };

  return {clickOnItem};
};

module.exports = Breadcrumb;
