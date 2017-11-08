/* global describe, it, expect */
'use strict';

define(
    ['pim/remover/job-instance-import'],
    function (JobInstanceRemover) {
        describe('Job Instance Import Remover', function () {

            it('returns an URL without query parameter', function () {
                expect(JobInstanceRemover.getUrl('foo')).toBe('/job_instance/rest/import/foo');
            });
        });
    }
);
