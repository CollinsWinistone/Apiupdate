<?php

namespace App\Http\Request;

use Illuminate\Http\Request;

class PaginationOptions
{
    const DEFAULT_PER_PAGE = 25;

    /**
     * @var string
     */
    protected $page;

    /**
     * @var string
     */
    protected $perPage;

    /**
     * @return string
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return string
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * PaginationOptions constructor.
     * @param string|null $page
     * @param string|null $perPage
     */
    public function __construct($page = null, $perPage = null)
    {
        $this->page = (int)($page ?: 1);
        $this->perPage = (int)(!is_null($perPage) ? $perPage : self::DEFAULT_PER_PAGE);
    }

    /**
     * @param Request $request
     * @return PaginationOptions
     */
    public static function fromRequest(Request $request)
    {
        return new self(
            $request->input('page'),
            $request->input('perPage')
        );
    }
}
