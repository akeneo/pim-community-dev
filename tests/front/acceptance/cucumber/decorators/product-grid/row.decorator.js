const Row = async (nodeElement) => {
  const getTitle = async () => {
    const title = await nodeElement.$('[data-column="label"]')
    const text = await (await title.getProperty('textContent')).jsonValue();

    return text.trim();
  };

  return { getTitle };
};

module.exports = Row;
