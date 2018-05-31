import user, {UserState} from 'akeneoenrichedentity/application/reducer/user';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';

export interface State {
  user: UserState;
  enrichedEntity: EnrichedEntity | null;
}

export default {
  user,
  enrichedEntity: (
    state: EnrichedEntity | null = null,
    action: {type: string; enrichedEntity: EnrichedEntity}
  ): EnrichedEntity | null => {
    switch (action.type) {
      case 'ENRICHED_ENTITY_RECEIVED':
        state = action.enrichedEntity;
        break;
      default:
        break;
    }

    return state;
  },
};
