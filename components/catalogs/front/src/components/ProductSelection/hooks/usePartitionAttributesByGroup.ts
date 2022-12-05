import {useMemo} from 'react';
import {CriterionFactory} from '../models/CriterionFactory';

type AttributeGroupPartition = {
    code: string;
    label: string;
    attributesWithFactories: CriterionFactory[];
};

export const usePartitionAttributesByGroup = (
    attributesWithFactories: CriterionFactory[] | undefined
): AttributeGroupPartition[] => {
    return useMemo(() => {
        if (attributesWithFactories === undefined) {
            return [];
        }

        const partitions: AttributeGroupPartition[] = [];
        attributesWithFactories.forEach(attributeWithFactory => {
            const groupCode = attributeWithFactory.group_code;
            let partition = partitions.find(partition => partition.code === groupCode);
            if (partition === undefined) {
                partition = {
                    code: groupCode,
                    label: attributeWithFactory.group_label,
                    attributesWithFactories: [],
                };

                partitions.push(partition);
            }

            partition.attributesWithFactories.push(attributeWithFactory);
        });

        return partitions;
    }, [attributesWithFactories]);
};
