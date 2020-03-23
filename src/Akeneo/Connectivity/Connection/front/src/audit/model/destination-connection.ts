import {FlowType} from '../../model/flow-type.enum';
import {Connection} from '../../model/connection';

export type DestinationConnection = Connection & {
    flowType: FlowType.DATA_DESTINATION;
};
