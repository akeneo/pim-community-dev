type ConnectionBusinessErrorCount = {
    connectionCode: string;
    errorCount: number;
};

export const useBusinessErrorCountPerConnection = () => {
    const data: ConnectionBusinessErrorCount[] = [];

    const sortedData = data.sort((a, b) => (a.errorCount <= b.errorCount ? 1 : -1));

    return {loading: false, errorCountPerConnection: sortedData};
};
