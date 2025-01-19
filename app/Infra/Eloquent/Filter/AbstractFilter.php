<?php


namespace App\Infra\Eloquent\Filter;

use App\Infra\Eloquent\Filter\CommonFilters\Keyword;
use EloquentFilter\ModelFilter;

class AbstractFilter extends ModelFilter
{

    /**
     * @var array
     */
    protected $allowedFilters = [];

    /**
     * @var array
     */
    protected $keywordFields = [];

    /**
     * @var array
     */
    protected $columnMap = [];

    /**
     * @return void
     */
    public function setup()
    {
        $this->select(sprintf('%s.*', $this->query->getModel()->getTable()));
    }

    /**
     * @return void
     */
    public function trash($value)
    {
        return $this->onlyTrashed();
    }


    /**
     * @return void
     */
    public function filterInput()
    {

        foreach ($this->input as $key => $val) {

            if (is_string($val) && strpos($val, ',') !== false) {
                $val = explode(',', $val);
            }

            $method = $this->getFilterMethod($key);

            if ($this->methodIsCallable($method)) {
                $this->{$method}($val);
            } else {
                if (in_array($method, $this->allowedFilters)) {
                    $column = $this->resolveColumn($method) ?? sprintf('%s.%s', $this->query->getModel()->getTable(), $key);
                    if (is_array($val)) {
                        $this->whereIn($column, $val);
                    } else {
                        $this->where($column, $val);
                    }
                }
            }
        }
    }

    /**
     * @param string $method
     * @return string|null
     */
    private function resolveColumn(string $method): ?string
    {
        if (!isset($this->columnMap[$method])) {
            return null;
        }
        return $this->columnMap[$method];
    }

    /**
     * @param string $value
     * @return void
     */
    public function search($value)
    {

        if (empty($this->keywordFields)) {
            return;
        }

        return Keyword::searchOn($this->keywordFields)(
            $this->query,
            $value,
            ""
        );
    }
}
