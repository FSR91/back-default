<?php

namespace  App\Infra\Eloquent\Filter\CommonFilters;

use Illuminate\Database\Eloquent\Builder;

class Keyword
{
    protected $searchableColumns = [];

    public function __construct($searchableColumns)
    {
        $this->searchableColumns = $searchableColumns;
    }

    static function searchOn($searchableColumns): self
    {
        return new static($searchableColumns);
    }

    public function __invoke(Builder $query, $value, string $property)
    {
        return $query->where(function (Builder $query) use ($value) {
            foreach ($this->searchableColumns as $column) {
                $query->orWhere($query->qualifyColumn($column), 'like', '%' . $value . '%');
            }
        });
    }
}
