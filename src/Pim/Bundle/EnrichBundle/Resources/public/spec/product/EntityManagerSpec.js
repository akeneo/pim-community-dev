/* global describe, it, expect, spyOn, beforeEach */
'use strict';

define(
    ['pim/entity-manager', 'pim/attribute-repository', 'pim/entity-repository'],
    function (EntityManager, AttributeRepository, EntityRepository) {
        describe('Entity manager', function () {

            beforeEach(function (done) {
                EntityManager.initialize().done(done);
            });

            it('exposes entity repositories', function () {
                expect(EntityManager.getRepository).toBeDefined();
            });

            it('returns the requested repository', function () {
                var repository = EntityManager.getRepository('attribute');
                expect(repository instanceof AttributeRepository).toBe(true);
            });

            it('returns the default entity repository if a custom repository is not defined', function () {
                var repository = EntityManager.getRepository('foo');
                expect(repository instanceof EntityRepository).toBe(true);
            });

            it('can clear the repository cache', function () {
                var repository = EntityManager.getRepository('attribute');
                spyOn(repository, 'clear');

                EntityManager.clear('attribute', 'name');
                expect(repository.clear).toHaveBeenCalledWith('name');

                EntityManager.clear('attribute');
                expect(repository.clear).toHaveBeenCalledWith(undefined);
            });
        });
    }
);
