import {arrayUnique} from 'akeneo-design-system';
import {AvailableSource, AvailableSourceGroup} from '../../../models';

type DropdownItem = {
  type: 'section' | 'source';
  code: string;
  label: string;
  sourceType: string;
};

const flattenSections = (groups: AvailableSourceGroup[]) => {
  const mergeGroups = groups.reduce<AvailableSourceGroup[]>((existingGroups, groupToAdd) => {
    const existingGroupWithSameCode = existingGroups.find(existingGroup => existingGroup.code === groupToAdd.code);
    if (undefined === existingGroupWithSameCode) {
      return groupToAdd.children.length === 0 ? existingGroups : [...existingGroups, groupToAdd];
    }

    existingGroupWithSameCode.children = arrayUnique<AvailableSource>(
      [...existingGroupWithSameCode.children, ...groupToAdd.children],
      (first, second) => first.code === second.code
    );

    return existingGroups;
  }, []);

  return mergeGroups.reduce<DropdownItem[]>((result, group) => {
    const sectionToAdd = {code: group.code, label: group.label, sourceType: '', type: 'section'} as DropdownItem;
    const sourcesToAdd = group.children.map(source => ({
      ...source,
      sourceType: source.type,
      type: 'source',
    })) as DropdownItem[];

    return [...result, sectionToAdd, ...sourcesToAdd];
  }, []);
};

export {flattenSections};
