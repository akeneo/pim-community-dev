function createLabels({
    locales = {},
    addPrefix = true,
    addChangeset = false
}) {
    const labels = {};
    const prefix = addPrefix ? 'label-' : '';

    Object.keys(locales).forEach(locale => {
        const labelName = prefix + locale;
        const value = locales[locale];
        labels[labelName] = addChangeset ? { old: value, 'new': value } : value;
    });

    return labels;
}

/**
 * Generate an association type
 *
 * Example:
 *
 * const createAssociationType = require('../../factory/association-type');
 * createAssociationType('Similar', { en_AU: 'Simila })
 *
 * @param {String} code
 * @param {Object} locales
 * @returns {Object}
 */
module.exports = (
    code = 'code',
    locales = {
        en_US: 'Type description',
        fr_FR: 'Description de type',
        de_DE: 'Type'
    }
) => {
    const author = 'System';
    const updatedDate = new Date();
    const id = '123-456';
    const labels = createLabels({ locales, addPrefix: false });
    const labelSnapshot = createLabels({ locales, addChangeset: true });
    const labelChangeset = createLabels({ locales });

    return {
        code,
        labels,
        meta: {
            id,
            form: 'pim-association-type-edit-form',
            model_type: 'association_type',
            created: {
                id,
                author,
                resource_id: id,
                snapshot: { code, ...labelSnapshot },
                changeset: {
                    code: { old: '', new: code },
                    ...labelChangeset
                },
                context: null,
                version: 1,
                logged_at: updatedDate,
                pending: false
            },
            updated: {
                id,
                author,
                resource_id: id,
                snapshot: { code, ...labelSnapshot },
                changeset: {
                    code: { old: '', 'new': code },
                    ...labelChangeset
                },
                context: null,
                version: 1,
                logged_at: updatedDate,
                pending: false
            }
        }
    };
};
