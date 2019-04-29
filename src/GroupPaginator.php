<?php

namespace GrofGraf\LaravelGroupPagination;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Input;

class GroupPaginator extends \Illuminate\Pagination\Paginator
{

    public static function resolveCurrentPage($pageName = 'page', $default = null, $pages = [])
    {
        //$default = count($this->pages) ? $this->pages[0] : 1;
        $page = Input::get($pageName);
        $pageExists = collect($pages)->search(function($i) use ($page){
          return $i == $page;
        });
        if($page && $pageExists !== false){
          return $page;
        }elseif(count($pages)){
          return collect($pages)->first();
        }

        return 1;
    }
}
