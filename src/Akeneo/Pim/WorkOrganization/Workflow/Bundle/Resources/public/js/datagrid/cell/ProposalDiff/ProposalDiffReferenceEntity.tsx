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

const ProposalDiffRecordView = styled(RecordView)<{$accessor: ProposalChangeAccessor} & AkeneoThemedProps>`
  .record-selector-container > .record-selector {
    width: calc(100% - 28px);
    & > a {
      background: ${({$accessor}) => ($accessor === 'before' ? getColor('red', 20) : getColor('green', 20))};
      cursor: default;
    }
  }
`;

type ProposalDiffReferenceEntityProps = {
  accessor: ProposalChangeAccessor;
  change: {
    attributeReferenceDataName: string;
    before: string | null;
    after: string | null;
  };
};

const ProposalDiffReferenceEntity: React.FC<ProposalDiffReferenceEntityProps> = ({accessor, change, ...rest}) => {
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

  const value = createValue(
    attribute,
    ChannelReference.create(null),
    LocaleReference.create(null),
    denormalizeRecordData(change[accessor])
  );

  return (
    <ProposalDiffRecordView
      $accessor={accessor}
      value={value}
      locale={LocaleReference.create(UserContext.get('catalogLocale'))}
      onChange={() => {}}
      canEditData={false}
      channel={ChannelReference.create(UserContext.get('catalogScope'))}
      {...rest}
    />
  );
};

class ProposalDiffReferenceEntityMatcher {
  static supports(attributeType: string) {
    return [
      'akeneo_reference_entity',
    ].includes(attributeType);
  }

  static render() {
    return ProposalDiffReferenceEntity;
  }
}

export {ProposalDiffReferenceEntityMatcher};
