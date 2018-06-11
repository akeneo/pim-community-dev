import * as React from 'react';
import ItemView from 'akeneoenrichedentity/application/component/enriched-entity/index/item';
import EnrichedEntity, {createEnrichedEntity} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import {createIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';


interface TableState {
  locale: string;
  enrichedEntities: EnrichedEntity[];
}

interface TableDispatch {
  onRedirectToEnrichedEntity: (enrichedEntity: EnrichedEntity) => void;
}

interface TableProps extends TableState, TableDispatch {}

export default class Table extends React.Component<TableProps, {nextItemToAddPosition: number}> {
  constructor(props: TableProps) {
    super(props);

    this.state = {
      nextItemToAddPosition: 0,
    };
  }

  componentWillReceiveProps(nextProps: TableProps) {
    if (this.props.enrichedEntities.length !== nextProps.enrichedEntities.length) {
      this.setState({nextItemToAddPosition: this.props.enrichedEntities.length});
    }
  }

  renderItems(
    enrichedEntities: EnrichedEntity[],
    locale: string,
    onRedirectToEnrichedEntity: (enrichedEntity: EnrichedEntity) => void
  ): JSX.Element | JSX.Element[] {
    if (0 === enrichedEntities.length) {
      const enrichedEntityIdentifier = createIdentifier('');
      const labelCollection = createLabelCollection({});
      const enrichedEntity = createEnrichedEntity(enrichedEntityIdentifier, labelCollection);

      return (
        <ItemView
          isLoading={true}
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
    })
  }

  render(): JSX.Element | JSX.Element[] {
    const {
      enrichedEntities,
      locale,
      onRedirectToEnrichedEntity,
    } = this.props;

    return (
      <table className="AknGrid">
        <tbody className="AknGrid-body">
          {this.renderItems(enrichedEntities, locale, onRedirectToEnrichedEntity)}
        </tbody>
      </table>
    );
  }
}
