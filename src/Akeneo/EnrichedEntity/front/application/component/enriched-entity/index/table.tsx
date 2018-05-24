import * as React from 'react';
import ItemView from 'akeneoenrichedentity/application/component/enriched-entity/index/item';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';

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

  render(): JSX.Element | JSX.Element[] {
    const {
      enrichedEntities,
      locale,
      onRedirectToEnrichedEntity,
    } = this.props;

    return (
      <table className="AknGrid">
        <tbody className="AknGrid-body">
          {enrichedEntities.map((enrichedEntity: EnrichedEntity, index: number) => {
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
          })}
        </tbody>
      </table>
    );
  }
}
