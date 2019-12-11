import {FlowType} from '../../../domain/apps/flow-type.enum';
import {App} from '../../../domain/apps/app.interface';

export interface SourceApp extends App {
    flowType: FlowType.DATA_SOURCE;
}
