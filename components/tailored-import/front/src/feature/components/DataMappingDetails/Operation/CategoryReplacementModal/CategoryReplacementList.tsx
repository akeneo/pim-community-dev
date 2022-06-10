import {Table, TagInput} from "akeneo-design-system";
import React from "react";
import {CategoryTree} from "../../../../models/Category";
import {getLabel, useUserContext} from "@akeneo-pim-community/shared";

type CategoryReplacementListProps = {
  categoryTree: CategoryTree
};

const CategoryReplacementList = ({
  categoryTree
}: CategoryReplacementListProps) => {
  const catalogLocale = useUserContext().get('catalogLocale');

  return (
    <Table.Row>
      <Table.Cell>{getLabel(categoryTree.labels, catalogLocale, categoryTree.code)}</Table.Cell>
      <Table.Cell>
        <TagInput
          onChange={function noRefCheck(){}}
          onSubmit={function noRefCheck(){}}
          placeholder="Placeholder"
          value={[]}
        />
      </Table.Cell>
    </Table.Row>
  )
}

export {CategoryReplacementList};
