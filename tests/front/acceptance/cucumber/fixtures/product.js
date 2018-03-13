const createProduct = (
    identifier,
    values = [],
    enabled = true,
    family = 'scanner',
    label = {},
    model_type = 'product',
    image = null,
    completenesses = []
) => {
    return {
        identifier,
        values,
        enabled,
        family,
        meta: {
            label,
            model_type,
            image,
            completenesses
        }
    };
};

const createProductWithLabels = (identifier, labels) => {
    return createProduct(identifier, undefined, undefined, undefined, labels);
};

module.exports = { createProduct, createProductWithLabels };
