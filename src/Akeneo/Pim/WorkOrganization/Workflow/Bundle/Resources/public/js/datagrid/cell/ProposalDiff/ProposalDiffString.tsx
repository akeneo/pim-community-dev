import React from 'react';
import {diffChars, Change} from 'diff';
import {ProposalChangeAccessor} from '../ProposalChange';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {ArrowDownIcon, LoaderIcon} from 'akeneo-design-system';

const CHAR_LIMIT = 512;

const ExpandButton = styled.span`
  cursor: pointer;
`;

type ProposalDiffStringProps = {
  accessor: ProposalChangeAccessor;
  change: {
    before: string | null;
    after: string | null;
  };
};

/**
 * collapsed: the text to display is too long, we truncate it to compute the diff to save time
 * expand: the text to display is ok, but the other text to compare is truncated to save time
 * force_expand: no matter the text lengths, we calculate the full diff (can be long)
 */
type DiffState = 'collapsed' | 'expand' | 'force_expand';

const computeDiffState = (
  change: {
    before: string | null;
    after: string | null;
  },
  accessor: ProposalChangeAccessor
): DiffState => {
  let before = change.before || '';
  let after = change.after || '';
  if (before.length > CHAR_LIMIT || after.length > CHAR_LIMIT) {
    before = before.substring(0, CHAR_LIMIT - 3);
    after = after.substring(0, CHAR_LIMIT - 3);
  }

  return (accessor === 'before' && before !== (change.before || '')) ||
    (accessor === 'after' && after !== (change.after || ''))
    ? 'collapsed'
    : 'expand';
};

const ProposalDiffString: React.FC<ProposalDiffStringProps> = ({accessor, change, ...rest}) => {
  const [changes, setChanges] = React.useState<Change[] | null>(null);
  const [diffState, setDiffState] = React.useState<DiffState>(computeDiffState(change, accessor));
  const translate = useTranslate();

  React.useEffect(() => {
    let before = change.before || '';
    let after = change.after || '';
    switch (diffState) {
      case 'collapsed':
        before = before.substring(0, CHAR_LIMIT - 3);
        after = after.substring(0, CHAR_LIMIT - 3);
        break;
      case 'expand':
        before = accessor === 'after' ? before.substring(0, CHAR_LIMIT) : before;
        after = accessor === 'before' ? after.substring(0, CHAR_LIMIT) : after;
        break;
    }
    setChanges(diffChars(before, after));
  }, [diffState]);

  const forceExpand = () => {
    setChanges(null);
    setDiffState('force_expand');
  };

  return (
    <span {...rest}>
      {!changes && <LoaderIcon />}
      {changes &&
        changes.map((change, i) => {
          if (accessor === 'before' && change.removed) {
            return <del key={i}>{change.value}</del>;
          }
          if (accessor === 'after' && change.added) {
            return <ins key={i}>{change.value}</ins>;
          }
          if ((accessor === 'before' && !change.added) || (accessor === 'after' && !change.removed)) {
            return change.value;
          }
          return null;
        })}
      {changes && 'collapsed' === diffState && (
        <>
          ...
          <div>
            <ExpandButton onClick={forceExpand}>
              <ArrowDownIcon size={10} /> {translate('pim_datagrid.workflow.see_more')}
            </ExpandButton>
          </div>
        </>
      )}
    </span>
  );
};

class ProposalDiffStringMatcher {
  static supports(attributeType: string) {
    return [
      'pim_catalog_text',
      'pim_catalog_identifier',
      'pim_catalog_textarea',
      'pim_catalog_simpleselect',
      'pim_reference_data_simpleselect',
      'pim_catalog_date',
      'pim_catalog_number',
      'pim_catalog_boolean',
    ].includes(attributeType);
  }

  static render() {
    return ProposalDiffString;
  }
}

export {ProposalDiffStringMatcher};
