/* global describe, it, expect, spyOn, beforeEach */


import FetcherRegistry from 'pim/fetcher-registry'
import AttributeFetcher from 'pim/attribute-fetcher'
import BaseFetcher from 'pim/base-fetcher'
describe('Entity manager', function () {

    beforeEach(function (done) {
        FetcherRegistry.initialize().done(done)
    })

    it('exposes object fetcher', function () {
        expect(FetcherRegistry.getFetcher).toBeDefined()
    })

    it('returns the requested fetcher', function () {
        var fetcher = FetcherRegistry.getFetcher('attribute')
        expect(fetcher instanceof AttributeFetcher).toBe(true)
    })

    it('returns the default entity fetcher if a custom fetcher is not defined', function () {
        var fetcher = FetcherRegistry.getFetcher('foo')
        expect(fetcher instanceof BaseFetcher).toBe(true)
    })

    it('can clear the fetcher cache', function () {
        var fetcher = FetcherRegistry.getFetcher('attribute')
        spyOn(fetcher, 'clear')

        FetcherRegistry.clear('attribute', 'name')
        expect(fetcher.clear).toHaveBeenCalledWith('name')

        FetcherRegistry.clear('attribute')
        expect(fetcher.clear).toHaveBeenCalledWith(undefined)
    })
})

