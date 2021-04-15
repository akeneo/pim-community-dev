import React from 'react';
import {view as RecordView} from 'akeneoreferenceentity/application/component/record/edit/enrich/data/record';
import ChannelReference from 'akeneoreferenceentity/domain/model/channel-reference';
import LocaleReference from 'akeneoreferenceentity/domain/model/locale-reference';
import {createValue} from 'akeneoreferenceentity/domain/model/record/value';
import {denormalize as denormalizeRecordAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/record';
import {denormalize as denormalizeRecordData} from 'akeneoreferenceentity/domain/model/record/data/record';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor} from 'akeneo-design-system';
import { ProposalChangeAccessor } from "../ProposalChange";
const UserContext = require('pim/user-context');

const ProposalDiffRecordView = styled(RecordView)<{$state?: 'removed' | 'added'} & AkeneoThemedProps>`
  margin-top: 5px;

  .record-selector-container > .record-selector {
    width: calc(100% - 28px);
    & > a {
      background: ${({$state}) => {
        if ($state === 'removed') {
          return getColor('red', 20);
        } else if ($state === 'added') {
          return getColor('green', 20);
        }
        return 'none';
      }};
      cursor: default;
    }
  }
`;

type ProposalDiffReferenceEntityCollectionProps = {
  accessor: ProposalChangeAccessor;
  change: {
    attributeReferenceDataName: string;
    before: string[] | null;
    after: string[] | null;
  };
};

const ProposalDiffReferenceEntityCollection: React.FC<ProposalDiffReferenceEntityCollectionProps> = ({
  accessor,
  change,
  ...rest
}) => {
  if (!change[accessor]) {
    return <span {...rest}/>;
  }

  const attribute = denormalizeRecordAttribute({
    type: 'record',
    record_type: change.attributeReferenceDataName,
    reference_entity_identifier: 'fake_reference_entity_identifier',
    code: 'fake_code',
    identifier: 'fake_identifier',
    is_required: false,
    labels: {},
    order: 0,
    value_per_channel: false,
    value_per_locale: false,
  });

  return (
    <div {...rest}>
      {(change[accessor] as string[]).map((data, i) => {
        const value = createValue(
          attribute,
          ChannelReference.create(null),
          LocaleReference.create(null),
          denormalizeRecordData(data)
        );

        const isDiff =
          accessor === 'before'
            ? !(change['after'] || []).includes((change['before'] || [])[i])
            : !(change['before'] || []).includes((change['after'] || [])[i]);

        return (
          <ProposalDiffRecordView
            key={data}
            $state={isDiff ? (accessor === 'before' ? 'removed' : 'added') : undefined}
            value={value}
            locale={LocaleReference.create(UserContext.get('catalogLocale'))}
            onChange={() => {}}
            canEditData={false}
            channel={ChannelReference.create(UserContext.get('catalogScope'))}
          />
        );
      })}
    </div>
  );
};

class ProposalDiffReferenceEntityCollectionMatcher {
  static supports(attributeType: string) {
    return [
      'akeneo_reference_entity_collection',
    ].includes(attributeType);
  }

  static render() {
    return ProposalDiffReferenceEntityCollection;
  }
}

export {ProposalDiffReferenceEntityCollectionMatcher};
