import user, {UserState} from 'akeneoenrichedentity/application/reducer/user';
import sidebar, {SidebarState} from 'akeneoenrichedentity/application/reducer/sidebar';
import grid, {GridState} from 'akeneoenrichedentity/application/reducer/grid';
import createRecord, {CreateRecordState} from 'akeneoenrichedentity/application/reducer/record/create';
import editForm from 'akeneoenrichedentity/application/reducer/enriched-entity/edit-form';
import {FormState} from 'akeneoenrichedentity/application/reducer/form-state';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import Record from 'akeneoenrichedentity/domain/model/record/record';

export interface State {
  user: UserState;
  sidebar: SidebarState;
  grid: GridState<Record>;
  enrichedEntity: EnrichedEntity | null;
  editForm: FormState;
  createRecord: CreateRecordState;
}

export default {
  user,
  sidebar,
  grid,
  enrichedEntity: (
    state: EnrichedEntity | null = null,
    action: {type: string; enrichedEntity: EnrichedEntity}
  ): EnrichedEntity | null => {
    switch (action.type) {
      case 'ENRICHED_ENTITY_RECEIVED':
        state = action.enrichedEntity;
        break;
      case 'ENRICHED_ENTITY_UPDATED':
        state = action.enrichedEntity;
        break;
      default:
        break;
    }

    return state;
  },
  editForm,
  createRecord,
};
