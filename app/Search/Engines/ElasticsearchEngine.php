<?php

namespace App\Search\Engines;

use Elastic\Elasticsearch\Client;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;

class ElasticsearchEngine extends Engine
{
    protected Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Update the given model in the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function update($models)
    {
        $params = ['body' => []];

        foreach ($models as $model) {
            $array = $model->toSearchableArray();

            if (empty($array)) {
                continue;
            }

            $params['body'][] = [
                'index' => [
                    '_index' => $model->searchableAs(),
                    '_id' => $model->getScoutKey(),
                ],
            ];

            $params['body'][] = $array;
        }

        if (!empty($params['body'])) {
            try {
                $this->client->bulk($params);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Elasticsearch error: " . $e->getMessage());
            }
        }
    }

    /**
     * Remove the given model from the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function delete($models)
    {
        $params = ['body' => []];

        foreach ($models as $model) {
            $params['body'][] = [
                'delete' => [
                    '_index' => $model->searchableAs(),
                    '_id' => $model->getScoutKey(),
                ],
            ];
        }

        if (!empty($params['body'])) {
            try {
                $this->client->bulk($params);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Elasticsearch error: " . $e->getMessage());
            }
        }
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @return mixed
     */
    public function search(Builder $builder)
    {
        try {
            return $this->performSearch($builder, array_filter([
                'size' => $builder->limit,
            ]));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Elasticsearch Search failed: " . $e->getMessage());
            return ['hits' => ['total' => ['value' => 0], 'hits' => []]];
        }
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @param  int  $perPage
     * @param  int  $page
     * @return mixed
     */
    public function paginate(Builder $builder, $perPage, $page)
    {
        try {
            return $this->performSearch($builder, [
                'from' => ($page - 1) * $perPage,
                'size' => $perPage,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Elasticsearch Pagination failed: " . $e->getMessage());
            return ['hits' => ['total' => ['value' => 0], 'hits' => []]];
        }
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @param  array  $options
     * @return mixed
     */
    protected function performSearch(Builder $builder, array $options = [])
    {
        $params = [
            'index' => $builder->model->searchableAs(),
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'multi_match' => [
                                    'query' => $builder->query,
                                    'fields' => ['name', 'description'],
                                    'fuzziness' => 'AUTO',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $params = array_merge($params, $options);

        // Filters
        foreach ($builder->wheres as $column => $value) {
            $params['body']['query']['bool']['filter'][] = [
                'term' => [$column => $value],
            ];
        }

        return $this->client->search($params);
    }

    /**
     * Map the given results to instances of the given model.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @param  mixed  $results
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function map(Builder $builder, $results, $model)
    {
        if ($this->getTotalCount($results) === 0) {
            return $model->newCollection();
        }

        $ids = collect($results['hits']['hits'])->pluck('_id')->values()->all();

        return $model->getScoutModelsByIds(
            $builder,
            $ids
        )->filter(function ($model) use ($ids) {
            return in_array($model->getScoutKey(), $ids);
        })->sortBy(function ($model) use ($ids) {
            return array_search($model->getScoutKey(), $ids);
        })->values();
    }

    /**
     * Map the given results to instances of the given model via a lazy collection.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @param  mixed  $results
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Support\LazyCollection
     */
    public function lazyMap(Builder $builder, $results, $model)
    {
        if ($this->getTotalCount($results) === 0) {
            return \Illuminate\Support\LazyCollection::make($model->newCollection());
        }

        $ids = collect($results['hits']['hits'])->pluck('_id')->values()->all();

        return $model->getScoutModelsByIds(
            $builder,
            $ids
        )->filter(function ($model) use ($ids) {
            return in_array($model->getScoutKey(), $ids);
        })->sortBy(function ($model) use ($ids) {
            return array_search($model->getScoutKey(), $ids);
        })->values()->toLazy();
    }

    /**
     * Map the given results to a collection of primary keys.
     *
     * @param  mixed  $results
     * @return \Illuminate\Support\Collection
     */
    public function mapIds($results)
    {
        return collect($results['hits']['hits'])->pluck('_id')->values();
    }

    /**
     * Get the total count from a raw result returned by the engine.
     *
     * @param  mixed  $results
     * @return int
     */
    public function getTotalCount($results)
    {
        return $results['hits']['total']['value'];
    }

    /**
     * Flush all of the model's records from the engine.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function flush($model)
    {
        $this->client->indices()->delete([
            'index' => $model->searchableAs(),
            'ignore_unavailable' => true,
        ]);
    }

    /**
     * Create the given index.
     *
     * @param  string  $name
     * @param  array  $options
     * @return void
     */
    public function createIndex($name, array $options = [])
    {
        $this->client->indices()->create(['index' => $name]);
    }

    /**
     * Delete the given index.
     *
     * @param  string  $name
     * @return void
     */
    public function deleteIndex($name)
    {
        $this->client->indices()->delete(['index' => $name]);
    }
}
