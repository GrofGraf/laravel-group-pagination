<?php

namespace GrofGraf\LaravelTimePagination;

use Illuminate\Support\Collection;

class TimePaginator extends \Illuminate\Pagination\Paginator
{

    public static function resolveCurrentPage($pageName = 'page', $default = null, $pages = [])
    {
        //$default = count($this->pages) ? $this->pages[0] : 1;
        if (isset(static::$currentPageResolver)) {
          return call_user_func(static::$currentPageResolver, $pageName, $default, $pages);
        }
        return null;
    }
}
