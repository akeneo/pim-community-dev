const Header = async (nodeElement) => {
  const getTitle = async () => {
    const title =  await nodeElement.$('.AknTitleContainer-title')
    const text = await (await title.getProperty('textContent')).jsonValue();

    return text.trim();
  };

  return { getTitle };
};

module.exports = Header;
