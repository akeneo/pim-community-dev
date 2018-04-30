const Volume = async (nodeElement) => {
    const getType = async () => {
        const element = await nodeElement.$('[data-field]');
        const dataset = await (await element.getProperty('dataset'));

        return await (await dataset.getProperty('field')).jsonValue();
    };

    return { getType };
};

module.exports = Volume;
