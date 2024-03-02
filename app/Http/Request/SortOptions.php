<?php

namespace App\Http\Request;

use Illuminate\Http\Request;

class SortOptions
{
    const DEFAULT_DIRECTION = 'asc';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $dir;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * SortOptions constructor.
     * @param string $name
     * @param string('asc|desc')|null $dir
     */
    public function __construct($name, $dir = null)
    {
        $this->name = $name;
        $this->dir = $dir ?: self::DEFAULT_DIRECTION;
    }

    /**
     * @param Request $request
     * @return SortOptions[]
     */
    public static function fromRequest(Request $request)
    {
        if(! $request->has('orderBy')) {
            return [];
        }

        $sorters = [];

        $orderBy = $request->input('orderBy');

        if(is_array($orderBy)) {
            foreach ($orderBy as $item) {
                @list ($name, $dir) = explode(':', $item);
                $sorters[] = new SortOptions($name, $dir);
            }

            return $sorters;
        }

        @list ($name, $dir) = explode(':', $orderBy);

        return [
            new SortOptions($name, $dir)
        ];
    }
}
