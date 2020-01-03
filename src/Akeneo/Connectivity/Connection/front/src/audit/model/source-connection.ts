import {FlowType} from '../../model/flow-type.enum';
import {Connection} from '../../model/connection';

export type SourceConnection = Connection & {
    flowType: FlowType.DATA_SOURCE;
};
