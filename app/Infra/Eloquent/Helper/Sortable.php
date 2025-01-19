<?php

namespace App\Domains\Core\Infra\Eloquent\Helper;

use Illuminate\Support\Str;

trait Sortable
{

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array|string $sort
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSort($query, $sort)
    {
        if (empty($sort)) {
            return $query;
        }

        if (is_string($sort)) {
            $sort = explode(',', $sort);
        }

        array_map(function ($item) use ($query) {
            return $query->orderBy(
                str_replace('-', '', $item),
                Str::startsWith($item, '-') ? 'desc' : 'asc'
            );
        }, $sort);
    }
}
