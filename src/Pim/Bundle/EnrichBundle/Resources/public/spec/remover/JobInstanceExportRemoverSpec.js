/* global describe, it, expect */
'use strict';

define(
    ['pim/remover/job-instance-export'],
    function (JobInstanceRemover) {
        describe('Job Instance Export Remover', function () {

            it('returns an URL without query parameter', function () {
                expect(JobInstanceRemover.getUrl('foo')).toBe('/job_instance/rest/export/foo');
            });
        });
    }
);
