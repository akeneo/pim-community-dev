var isLabelCollection = function (labelCollection) {
    if (undefined === labelCollection || typeof labelCollection !== 'object') {
        return false;
    }
    return !Object.keys(labelCollection).some(function (key) { return typeof key !== 'string' || typeof labelCollection[key] !== 'string'; });
};
export { isLabelCollection };
//# sourceMappingURL=label-collection.js.map