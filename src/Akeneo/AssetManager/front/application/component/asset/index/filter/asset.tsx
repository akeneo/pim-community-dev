import * as React from 'react';
import {FilterView, FilterViewProps} from 'akeneoreferenceentity/application/configuration/value';
import {ConcreteRecordAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/record';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import {connect} from 'react-redux';
import __ from 'akeneoreferenceentity/tools/translator';
import {ConcreteRecordCollectionAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/record-collection';
import RecordSelector from 'akeneoreferenceentity/application/component/app/record-selector';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import {createLocaleReference} from 'akeneoreferenceentity/domain/model/locale-reference';
import {createChannelReference} from 'akeneoreferenceentity/domain/model/channel-reference';
import recordFetcher from 'akeneoreferenceentity/infrastructure/fetcher/record';
import {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import {getLabel} from 'pimui/js/i18n';
import {getAttributeFilterKey} from 'akeneoreferenceentity/tools/filter';

const memo = (React as any).memo;
const useState = (React as any).useState;
const useEffect = (React as any).useEffect;

type RecordFilterViewProps = FilterViewProps & {
  context: {
    locale: string;
    channel: string;
  };
};

const DEFAULT_OPERATOR = 'IN';

const RecordFilterView: FilterView = memo(({attribute, filter, onFilterUpdated, context}: RecordFilterViewProps) => {
  if (!(attribute instanceof ConcreteRecordAttribute || attribute instanceof ConcreteRecordCollectionAttribute)) {
    return null;
  }

  const [isOpen, setIsOpen] = useState(false);
  const [hydratedRecords, setHydratedRecords] = useState([]);

  const rawValues = undefined !== filter ? filter.value : [];
  const value = rawValues.map((recordCode: string) => RecordCode.create(recordCode));

  const updateHydratedRecords = async () => {
    if (0 < value.length) {
      const records = await recordFetcher.fetchByCodes(
        attribute.getRecordType().getReferenceEntityIdentifier(),
        value,
        context,
        true
      );

      setHydratedRecords(records);
    }
  };

  const emptyFilter = () => {
    setIsOpen(false);
    onFilterUpdated({
      field: getAttributeFilterKey(attribute),
      operator: DEFAULT_OPERATOR,
      value: [],
      context: {},
    });
  };

  useEffect(() => {
    updateHydratedRecords();
  });

  const hint =
    0 === value.length
      ? __('pim_reference_entity.record.grid.filter.option.all')
      : hydratedRecords
          .map((record: NormalizedRecord) => getLabel(record.labels, context.locale, record.code))
          .join(', ');

  return (
    <React.Fragment>
      <span className="AknFilterBox-filterLabel" onClick={() => setIsOpen(true)}>
        {attribute.getLabel(context.locale)}
      </span>
      <span
        className="AknFilterBox-filterCriteria AknFilterBox-filterCriteria--limited"
        onClick={() => setIsOpen(true)}
      >
        <span className="AknFilterBox-filterCriteriaHint" title={hint}>
          {hint}
        </span>
        <span className="AknFilterBox-filterCaret" />
      </span>
      {isOpen ? (
        <div>
          <div className="AknDropdown-mask" onClick={() => setIsOpen(false)} />
          <div className="AknFilterBox-filterDetails">
            <div className="AknFilterChoice">
              <div className="AknFilterChoice-header">
                <div className="AknFilterChoice-title">{attribute.getLabel(context.locale)}</div>
                <div className="AknIconButton AknIconButton--erase" onClick={emptyFilter} />
              </div>
              <RecordSelector
                value={value}
                referenceEntityIdentifier={attribute.getRecordType().getReferenceEntityIdentifier()}
                multiple={true}
                compact={true}
                locale={createLocaleReference(context.locale)}
                channel={createChannelReference(context.channel)}
                onChange={(recordCodes: RecordCode[]) => {
                  onFilterUpdated({
                    field: getAttributeFilterKey(attribute),
                    operator: DEFAULT_OPERATOR,
                    value: recordCodes.map((recordCode: RecordCode) => recordCode.stringValue()),
                    context: {},
                  });
                }}
              />
            </div>
          </div>
        </div>
      ) : null}
    </React.Fragment>
  );
});

export const filter = connect(
  (state: EditState, ownProps: FilterViewProps): RecordFilterViewProps => {
    return {
      ...ownProps,
      context: {
        locale: state.user.catalogLocale,
        channel: state.user.catalogChannel,
      },
    };
  }
)(RecordFilterView);
