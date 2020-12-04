import React from 'react';
import styled from 'styled-components';
import {AttributeGroup, AttributeGroupCollection} from '@akeneo-pim-community/settings-ui/src/models';

const translate = require('oro/translator');

const Helper = styled.div`
  margin: 5px 0 10px 0;
  background-color: ${({theme}) => theme.color.blue10};
  padding: 10px 0px 10px 0;
  display: flex;

  a {
    color: ${({theme}) => theme.color.purple100};
    text-decoration: underline ${({theme}) => theme.color.purple100};
  }
`;

const HelperIcon = styled.div`
  margin-right: 10px;
  border-right: 1px #c7cbd4 solid;
  background-image: url(/bundles/pimui/images/icon-info.svg);
  background-repeat: no-repeat;
  background-size: 20px 20px;
  background-position: 10px 0;
  min-width: 40px;
`;

type Props = {
  evaluatedAttributeGroups: AttributeGroupCollection | null;
  allGroupsEvaluated: boolean;
  locale: string;
};

const AttributeGroupsHelper = ({evaluatedAttributeGroups, allGroupsEvaluated, locale}: Props) => {
  if (
    !allGroupsEvaluated &&
    (evaluatedAttributeGroups === null || Object.keys(evaluatedAttributeGroups).length === 0)
  ) {
    return <></>;
  }

  return (
    <Helper>
      <HelperIcon />
      <div>
        {allGroupsEvaluated ? (
          <span
            dangerouslySetInnerHTML={{
              __html: translate('akeneo_data_quality_insights.attribute_group.all_groups_evaluated', {
                link: 'https://help.akeneo.com/pim/serenity/articles/manage-data-quality.html',
              }),
            }}
          />
        ) : (
          <>
            {translate('akeneo_data_quality_insights.attribute_group.used_groups_helper')}&nbsp;
            <span data-testid="dqi-evaluated-attribute-groups">
              {evaluatedAttributeGroups &&
                Object.entries(evaluatedAttributeGroups)
                  .map(([_, group]: [string, AttributeGroup]) => group.labels[locale])
                  .join(', ')}
              .
            </span>
          </>
        )}
      </div>
    </Helper>
  );
};

export {AttributeGroupsHelper};
