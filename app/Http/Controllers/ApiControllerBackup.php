<?php

namespace App\Http\Controllers;

use Request;
use App\Http\Controllers\Controller;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class ApiController extends Controller {

    use \App\Http\Requests\ApiResponse;

    /**
     * Comme list of namespaces to include in fractal transformation response.
     *
     * @var string
     */
    protected $defaultIncludes = [];

    public function __construct()
    {
        $this->fractal = \App::make('League\Fractal\Manager');

        $this->fractal->setSerializer(new \Api\Transformers\ApiSerializer());

        $this->setIncludes(Request::get('includes'));

        $this->processExcludes(Request::get('excludes'));
    }

    protected function setIncludes($includes)
    {
        if ($includes != '') {
            $this->defaultIncludes = explode(',', $includes);
        }
    }

    protected function processExcludes($excludes)
    {
        if ($excludes != '') {
            $excludes = explode(',', $excludes);

            $this->defaultIncludes = array_filter($this->defaultIncludes, function ($namespace) use ($excludes) {
                return !in_array($namespace, $excludes);
            });
        }
    }

    protected function parseIncludes()
    {
        $this->fractal->parseIncludes($this->defaultIncludes);
    }

    protected function processItem($item, $transformer)
    {
        $resource = new Item($item, $transformer);

        $this->parseIncludes();

        $rootScope = $this->fractal->createData($resource);

        return $this->respondWithArray($rootScope->toArray());
    }

    protected function transformItem($item, $transformer)
    {
        $resource = new Item($item, $transformer);

        $this->parseIncludes();

        $rootScope = $this->fractal->createData($resource);

        return (object) $rootScope->toArray();
    }

    protected function processCollection($collection, $transformer, $namespace = 'items')
    {
        $resource = new Collection($collection, $transformer, 'items');

        $this->parseIncludes();

        $rootScope = $this->fractal->createData($resource);

        return $this->respondWithArray([$namespace => $rootScope->toArray()]);
    }

    protected function processPaginatedCollection($paginatedCollection, $transformer, $namespace = 'items')
    {
        $paginatedCollection->appends(Request::except('page', 'token'));

        $resource = new Collection($paginatedCollection, $transformer, 'items');

        $resource->setPaginator(new IlluminatePaginatorAdapter($paginatedCollection));

        $this->parseIncludes();

        $rootScope = $this->fractal->createData($resource);

        return $this->respondWithArray([$namespace => $rootScope->toArray()]);
    }

    protected function processPaginatedCollections($results)
    {
        $collections = [];

        foreach ($results as $namespace => $result) {
            $paginatedCollection = $result[0];

            $transformer = $result[1];

            $paginatedCollection->appends(Request::except('page', 'token'));

            $resource = new Collection($paginatedCollection, $transformer, 'items');

            $resource->setPaginator(new IlluminatePaginatorAdapter($paginatedCollection));

            $this->parseIncludes();

            $rootScope = $this->fractal->createData($resource);

            $collections[$namespace] = $rootScope->toArray();
        }

        if (empty($collections)) {
            return false;
        }

        return $this->respondWithArray($collections);
    }
}
