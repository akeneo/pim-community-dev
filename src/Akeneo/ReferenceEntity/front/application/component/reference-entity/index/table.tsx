import * as React from 'react';
import ItemView from 'akeneoreferenceentity/application/component/reference-entity/index/item';
import ReferenceEntity, {
  createReferenceEntity,
} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import {createEmptyFile} from 'akeneoreferenceentity/domain/model/file';

interface TableState {
  locale: string;
  referenceEntities: ReferenceEntity[];
  isLoading: boolean;
}

interface TableDispatch {
  onRedirectToReferenceEntity: (referenceEntity: ReferenceEntity) => void;
}

interface TableProps extends TableState, TableDispatch {}

export default class Table extends React.Component<TableProps, {nextItemToAddPosition: number}> {
  readonly state = {
    nextItemToAddPosition: 0,
  };

  componentWillReceiveProps(nextProps: TableProps) {
    if (this.props.referenceEntities.length !== nextProps.referenceEntities.length) {
      this.setState({nextItemToAddPosition: this.props.referenceEntities.length});
    }
  }

  renderItems(
    referenceEntities: ReferenceEntity[],
    locale: string,
    isLoading: boolean,
    onRedirectToReferenceEntity: (referenceEntity: ReferenceEntity) => void
  ): JSX.Element | JSX.Element[] {
    if (0 === referenceEntities.length && isLoading) {
      const referenceEntityIdentifier = createIdentifier('');
      const labelCollection = createLabelCollection({});
      const referenceEntity = createReferenceEntity(referenceEntityIdentifier, labelCollection, createEmptyFile());

      return Array(4)
        .fill('placeholder')
        .map((attributeIdentifier, key) => (
          <ItemView
            key={`${attributeIdentifier}_${key}`}
            isLoading={isLoading}
            referenceEntity={referenceEntity}
            locale={locale}
            onRedirectToReferenceEntity={() => {}}
            position={key}
          />
        ));
    }

    return referenceEntities.map((referenceEntity: ReferenceEntity, index: number) => {
      const itemPosition = index - this.state.nextItemToAddPosition;

      return (
        <ItemView
          key={referenceEntity.getIdentifier().stringValue()}
          referenceEntity={referenceEntity}
          locale={locale}
          onRedirectToReferenceEntity={onRedirectToReferenceEntity}
          position={itemPosition > 0 ? itemPosition : 0}
        />
      );
    });
  }

  render(): JSX.Element | JSX.Element[] {
    const {referenceEntities, locale, onRedirectToReferenceEntity, isLoading} = this.props;

    return (
      <div className="AknGrid">
        <div className="AknGrid-body">
          {this.renderItems(referenceEntities, locale, isLoading, onRedirectToReferenceEntity)}
        </div>
      </div>
    );
  }
}
