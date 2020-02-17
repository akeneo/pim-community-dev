const Image = require('./asset-preview/image');

const AssetPreview = async (nodeElement, createElementDecorator, page) => {
  const getImagePreviewed = async () => {
    const getElement = createElementDecorator({
      Image: {
        selector: `[data-role="media-data-preview"]`,
        decorator: Image,
      },
    });

    await page.waitFor(`[data-role="media-data-preview"]`);

    return await getElement(page, 'Image');
  };

  return {getImagePreviewed};
};

module.exports = AssetPreview;
