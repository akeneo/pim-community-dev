const Image = require('./asset-preview/image');

const AssetPreview = async (nodeElement, createElementDecorator, page) => {
  const getImagePreviewed = async () => {
    const getElement = createElementDecorator({
      Image: {
        selector: `[data-role="media-file-preview"]`,
        decorator: Image,
      },
    });

    await page.waitFor(`[data-role="media-file-preview"]`);

    return await getElement(page, 'Image');
  };

  return {getImagePreviewed};
};

module.exports = AssetPreview;
