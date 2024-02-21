import {useQuery} from '../../../shared/fetch';

type WeeklyErrorAuditData = {
    [connectionCode: string]: {
        previous_week: {[date: string]: number};
        current_week: {[date: string]: number};
        current_week_total: number;
    };
};

type WeeklyAuditData = {
    [connectionCode: string]: {
        daily: {[date: string]: number};
        weekly_total: number;
    };
};

const useWeeklyErrorAudit = () => {
    const {loading, data} = useQuery<WeeklyErrorAuditData>('akeneo_connectivity_connection_audit_rest_weekly_error');
    if (loading) {
        return {loading, weeklyErrorAuditData: {}};
    }

    const weeklyAuditData = Object.entries(data || {}).reduce(
        (weeklyAuditData, [connectionCode, connectionAuditData]) => {
            weeklyAuditData[connectionCode] = {
                daily: {
                    ...connectionAuditData.previous_week,
                    ...connectionAuditData.current_week,
                },
                weekly_total: connectionAuditData.current_week_total,
            };

            return weeklyAuditData;
        },
        {} as WeeklyAuditData
    );

    return {loading: false, weeklyErrorAuditData: weeklyAuditData};
};

export {useWeeklyErrorAudit};
