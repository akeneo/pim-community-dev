import {useRouter} from "@akeneo-pim-community/shared";


const useFetchSampleData = (): (job_code: string, column_index: string) => Promise<Array<string>> => {
    const router = useRouter();

    return (job_code: string, column_index: string): Promise<Array<string>> => {
        const route = router.generate('pimee_tailored_import_get_sample_data_action', {job_code, column_index});

        return new Promise<Array<string>>(async (resolve, reject) => {
                const response = await fetch(route, {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (response.ok) {
                    const sampleData = await response.json();
                    resolve(sampleData);
                }

                reject()
            }
        );
    }
}

export { useFetchSampleData };