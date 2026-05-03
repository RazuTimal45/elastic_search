<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\Elastic\Elasticsearch\Client::class, function () {
            return \Elastic\Elasticsearch\ClientBuilder::create()
                ->setHosts([config('services.elasticsearch.host')])
                ->build();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->make(\Laravel\Scout\EngineManager::class)->extend('elasticsearch', function ($app) {
            return new \App\Search\Engines\ElasticsearchEngine($app->make(\Elastic\Elasticsearch\Client::class));
        });
    }
}
