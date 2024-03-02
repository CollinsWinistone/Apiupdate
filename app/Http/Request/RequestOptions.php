<?php

namespace App\Http\Request;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class RequestOptions
{
    /**
     * @var PaginationOptions
     */
    protected $pagination;

    /**
     * @var SortOptions[]
     */
    protected $sorters;

    /**
     * @var FilterOptions[]
     */
    protected $filters;

    /**
     * RequestOptions constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        // Add debug statements
        Log::info('RequestOptions constructor called');
        Log::info('Request data:', $request->all());

        $this->pagination = PaginationOptions::fromRequest($request);
        $this->sorters = SortOptions::fromRequest($request);
        $this->filters = FilterOptions::fromRequest($request);
    }

    /**
     * @return PaginationOptions
     */
    public function getPaginationOptions()
    {
        return $this->pagination;
    }

    /**
     * @return SortOptions[]
     */
    public function getSortOptions()
    {
        return $this->sorters;
    }

    /**
     * @return FilterOptions[]
     */
    public function getFilterOptions()
    {
        return $this->filters;
    }

    /**
     * @return mixed
     */
    public function getFilterValue($key)
    {
        if ($this->filters) {
            foreach ($this->filters as $filter) {
                if ($filter->getName() == $key) {
                    return $filter->getValue();
                }
            }
        }
        return null;
    }

    public function addFilter($name, $value)
    {
        $this->filters[] = new FilterOptions($name, $value);
    }

    public function addSortOption($name, $dir)
    {
        $this->sorters[] = new SortOptions($name, $dir);
    }
}
