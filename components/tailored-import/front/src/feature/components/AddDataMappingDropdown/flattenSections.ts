import {arrayUnique} from 'akeneo-design-system';
import {AvailableTarget, AvailableTargetGroup} from '../../models';

type DropdownItem = {
  type: 'section' | 'target';
  code: string;
  label: string;
  targetType: string;
};

const flattenSections = (groups: AvailableTargetGroup[]) => {
  const mergeGroups = groups.reduce<AvailableTargetGroup[]>((existingGroups, groupToAdd) => {
    const existingGroupWithSameCode = existingGroups.find(existingGroup => existingGroup.code === groupToAdd.code);
    if (undefined === existingGroupWithSameCode) {
      return groupToAdd.children.length === 0 ? existingGroups : [...existingGroups, groupToAdd];
    }

    existingGroupWithSameCode.children = arrayUnique<AvailableTarget>(
      [...existingGroupWithSameCode.children, ...groupToAdd.children],
      (first, second) => first.code === second.code
    );

    return existingGroups;
  }, []);

  return mergeGroups.reduce<DropdownItem[]>((result, group) => {
    const sectionToAdd = {code: group.code, label: group.label, targetType: '', type: 'section'} as DropdownItem;
    const targetsToAdd = group.children.map(target => ({
      ...target,
      targetType: target.type,
      type: 'target',
    })) as DropdownItem[];

    return [...result, sectionToAdd, ...targetsToAdd];
  }, []);
};

export {flattenSections};
