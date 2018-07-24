import * as React from 'react';
import ItemView from 'akeneoenrichedentity/application/component/enriched-entity/index/item';
import EnrichedEntity, {createEnrichedEntity} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import {createIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';

interface TableState {
  locale: string;
  enrichedEntities: EnrichedEntity[];
  isLoading: boolean;
}

interface TableDispatch {
  onRedirectToEnrichedEntity: (enrichedEntity: EnrichedEntity) => void;
}

interface TableProps extends TableState, TableDispatch {}

export default class Table extends React.Component<TableProps, {nextItemToAddPosition: number}> {
  readonly state = {
    nextItemToAddPosition: 0,
  };

  componentWillReceiveProps(nextProps: TableProps) {
    if (this.props.enrichedEntities.length !== nextProps.enrichedEntities.length) {
      this.setState({nextItemToAddPosition: this.props.enrichedEntities.length});
    }
  }

  renderItems(
    enrichedEntities: EnrichedEntity[],
    locale: string,
    isLoading: boolean,
    onRedirectToEnrichedEntity: (enrichedEntity: EnrichedEntity) => void
  ): JSX.Element | JSX.Element[] {
    if (0 === enrichedEntities.length && isLoading) {
      const enrichedEntityIdentifier = createIdentifier('');
      const labelCollection = createLabelCollection({});
      const enrichedEntity = createEnrichedEntity(enrichedEntityIdentifier, labelCollection, null);

      return (
        <ItemView
          isLoading={isLoading}
          key={0}
          enrichedEntity={enrichedEntity}
          locale={locale}
          onRedirectToEnrichedEntity={() => {}}
          position={0}
        />
      );
    }

    return enrichedEntities.map((enrichedEntity: EnrichedEntity, index: number) => {
      const itemPosition = index - this.state.nextItemToAddPosition;

      return (
        <ItemView
          key={enrichedEntity.getIdentifier().stringValue()}
          enrichedEntity={enrichedEntity}
          locale={locale}
          onRedirectToEnrichedEntity={onRedirectToEnrichedEntity}
          position={itemPosition > 0 ? itemPosition : 0}
        />
      );
    });
  }

  render(): JSX.Element | JSX.Element[] {
    const {enrichedEntities, locale, onRedirectToEnrichedEntity, isLoading} = this.props;

    return (
      <div className="AknGrid">
        <div className="AknGrid-body">
          {this.renderItems(enrichedEntities, locale, isLoading, onRedirectToEnrichedEntity)}
        </div>
      </div>
    );
  }
}
