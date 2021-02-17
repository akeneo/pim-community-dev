import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import {getErrorsView} from 'akeneoreferenceentity/application/component/app/validation-error';
import {RecordAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/record';
import referenceEntityFetcher from 'akeneoreferenceentity/infrastructure/fetcher/reference-entity';
import ReferenceEntity from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';

type Props = {
  attribute: RecordAttribute;
  errors: ValidationError[];
  locale: string;
};

class RecordView extends React.Component<Props, {referenceEntity: ReferenceEntity | null}> {
  state = {referenceEntity: null};
  async componentDidMount() {
    this.updateReferenceEntity();
  }

  async componentDidUpdate(prevProps: Props) {
    if (!this.props.attribute.getRecordType().equals(prevProps.attribute.getRecordType())) {
      this.updateReferenceEntity();
    }
  }

  async updateReferenceEntity() {
    const referenceEntityResult = await referenceEntityFetcher.fetch(
      this.props.attribute.recordType.getReferenceEntityIdentifier()
    );
    this.setState({referenceEntity: referenceEntityResult.referenceEntity});
  }

  render() {
    const value =
      null !== this.state.referenceEntity
        ? (this.state.referenceEntity as any).getLabel(this.props.locale)
        : this.props.attribute.recordType.stringValue();

    return (
      <div className="AknFieldContainer--packed" data-code="recordType">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label className="AknFieldContainer-label" htmlFor="pim_reference_entity.attribute.edit.input.record_type">
            {__('pim_reference_entity.attribute.edit.input.record_type')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <input
            type="text"
            autoComplete="off"
            className="AknTextField AknTextField--light AknTextField--disabled"
            id="pim_reference_entity.attribute.edit.input.record_type"
            name="record_type"
            value={value}
            readOnly
            tabIndex={-1}
          />
        </div>
        {getErrorsView(this.props.errors, 'recordType')}
      </div>
    );
  }
}

export const view = RecordView;
