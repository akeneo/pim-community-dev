export type ProductMappingSchema = {
    '$id': string,
    $schema: string,
    $comment: string,
    title: string,
    description: string,
    type: string,
    properties: {
        [target: string]: {
            title?: string,
            description?: string,
            type: string,
        }
    }
};
