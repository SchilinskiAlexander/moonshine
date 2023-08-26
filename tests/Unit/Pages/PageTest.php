<?php

use MoonShine\Pages\Crud\FormPage;
use MoonShine\Pages\Crud\IndexPage;
use MoonShine\Pages\Crud\ShowPage;
use MoonShine\Tests\Fixtures\Resources\TestResourceBuilder;

uses()->group('pages');

beforeEach(function (): void {
    $this->resource = TestResourceBuilder::new();
});

it('page urls' , function () {
    expect($this->resource->getPages())
        ->findByUri('index-page')
        ->toBeInstanceOf(IndexPage::class)
        ->findByUri('form-page')
        ->toBeInstanceOf(FormPage::class)
        ->findByUri('show-page')
        ->toBeInstanceOf(ShowPage::class)
    ;
});

it('to page index', function () {
    expect(
        app('router')
            ->getRoutes()
            ->match(
                app('request')->create(to_page($this->resource, IndexPage::class)
            )
        )
    )
        ->getName()
        ->toBe('moonshine.resource.page')
        ->hasParameter('resourceUri')
        ->toBeTrue()
        ->hasParameter('pageUri')
        ->toBeTrue()
        ->parameter('resourceUri')
        ->toBe('test-resource')
        ->parameter('pageUri')
        ->toBe('index-page')
    ;
});

it('to page form', function () {

    $url = to_page($this->resource, FormPage::class, ['resourceItem' => 1]);

    expect(
        app('router')
            ->getRoutes()
            ->match(app('request')->create($url)
        )
    )
        ->getName()
        ->toBe('moonshine.resource.page')
        ->hasParameter('resourceUri')
        ->toBeTrue()
        ->hasParameter('pageUri')
        ->toBeTrue()
        ->parameter('resourceUri')
        ->toBe('test-resource')
        ->parameter('pageUri')
        ->toBe('form-page')
        ->and(parse_url($url))
        ->query
        ->toBe('resourceItem=1')
    ;
});

it('to page show', function () {

    $url = to_page($this->resource, ShowPage::class, ['resourceItem' => 1]);

    expect(
        app('router')
            ->getRoutes()
            ->match(
                app('request')->create($url)
        )
    )
        ->getName()
        ->toBe('moonshine.resource.page')
        ->hasParameter('resourceUri')
        ->toBeTrue()
        ->hasParameter('pageUri')
        ->toBeTrue()
        ->parameter('resourceUri')
        ->toBe('test-resource')
        ->parameter('pageUri')
        ->toBe('show-page')
        ->and(parse_url($url))
        ->query
        ->toBe('resourceItem=1')
    ;
});