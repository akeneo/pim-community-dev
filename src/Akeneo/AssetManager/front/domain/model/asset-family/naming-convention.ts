type NamingConvention = string;

export default NamingConvention;

export const denormalizeAssetFamilyNamingConvention = (normalizedNamingConvention: any): NamingConvention => {
    return null === normalizedNamingConvention
        ? '{}'
        : JSON.stringify(normalizedNamingConvention, null, 4)
        ;
};
