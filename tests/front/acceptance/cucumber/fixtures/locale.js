module.exports = code => {
    const regions = {de: 'Germany', fr: 'France', us: 'United States'};
    const languages = {de: 'German', fr: 'French', en: 'English'};
    const [language, region] = code.split('_');

    return {
        code,
        label: `${languages[language.toLowerCase()]} (${regions[region]})`,
        region: regions[region],
        language: languages[language.toLowerCase()]
    };
};
