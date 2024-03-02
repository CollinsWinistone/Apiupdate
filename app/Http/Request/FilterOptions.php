<?php

namespace App\Http\Request;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class FilterOptions
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * FilterOptions constructor.
     * @param string $name
     * @param mixed|null $value
     */
    public function __construct($name, $value = null)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @param Request $request
     * @return FilterOptions[]
     */
    public static function fromRequest(Request $request)
    {
        if(! $request->has('filter')) {
            return [];
        }

        $filters = [];
        $rawFilters = $request->input('filter');

        if(
            ! is_array($rawFilters)
            || !self::isAssoc($rawFilters)
        ) {
            throw new BadRequestHttpException('Invalid filter query');
        }

        foreach ($rawFilters as $name => $value) {
            $filters[] = new FilterOptions($name, $value);
        }

        return $filters;
    }

    /**
     * Check if array is associative
     * @param array $arr
     * @return bool
     */
    protected static function isAssoc(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
