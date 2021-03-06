<?php

namespace NovaListCard\Http\Controllers;

use Illuminate\Routing\Controller;
use Laravel\Nova\Nova;
use NovaListCard\Http\Requests\CardRequest;

/**
 * @psalm-suppress UndefinedClass
 */
class ResourceController extends Controller
{
    /**
     * @param  \NovaListCard\Http\Requests\CardRequest  $cardRequest
     * @return mixed
     * @throws \Throwable
     */
    public function __invoke(CardRequest $cardRequest)
    {
        $resource = $cardRequest->findResource();
        throw_if(!$resource);

        return $resource::indexQuery(
            $cardRequest,
            $cardRequest->prepareQuery($resource::newModel()->query())
        )
                        ->get()
                        ->mapInto($resource)
                        ->filter(function ($resource) use ($cardRequest) {
                            return $resource->authorizedToView($cardRequest);
                        })
                        ->map(callback: function ($resource) use ($cardRequest) {
                            return [
                                'resource'      => $resource->resource->toArray(),
                                'resourceName'  => $resource::uriKey(),
                                'resourceTitle' => $resource::label(),
                                'title'         => $resource->title(),
                                'subTitle'      => $resource->subtitle(),
                                'resourceId'    => $resource->getKey(),
                                'url'           => url(Nova::path().'/resources/'.$resource::uriKey().'/'.$resource->getKey()),
                                'avatar'        => $resource->resolveAvatarUrl($cardRequest),
                                'aggregate'     => data_get($resource, $cardRequest->aggregateColumn()),
                            ];
                        });
    }
}
