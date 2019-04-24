<?php

namespace GrofGraf\LaravelGroupPagination;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use \Illuminate\Pagination\Paginator;
use DB;

class GroupPaginationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
      /*GroupPaginator::currentPageResolver(function ($pageName = 'page', $default = 1, $pages = []) {
          $page = $this->app['request']->input($pageName);
          $page = collect($pages)->search(function($i) use ($page){
            return $i == $page;
          });
          if($page !== false){
            return $page + 1;
          }
      });*/
      GroupPaginator::currentPageResolver(function ($pageName = 'page', $default = 1, $pages = []) {
          $page = $this->app['request']->input($pageName);
          $pageExists = collect($pages)->search(function($i) use ($page){
            return $i == $page;
          });
          if($page && $pageExists !== false){
            return $page;
          }elseif(count($pages)){
            return collect($pages)->first();
          }

          return 1;
      });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

      Builder::macro('groupPaginate', function ($column, $start, $length, $order = 'asc', $format = 'Y-m-d', $pageName = 'page') {

        $query = $this->toBase()->orderBy($column, $order)->get(['*']);

        $total = $query->count();
        $pages = $query->groupBy(function($item, $key) use ($column, $start, $length){
          if(substr($item->{$column}, $start, $length)){
            return substr($item->{$column}, $start, $length);
          }else{
            throw new \Exception("The field is not in correct format");
          }
        });
        $page = GroupPaginator::resolveCurrentPage($pageName, 1, $pages->keys());
        $firstItem = 1;
        foreach($pages->keys() as $p){
          if($p != $page){
            $firstItem += $pages[$p]->count();
          }else{
            break;
          }
        }
        $items = $page !== false && isset($pages[$page]) ? $pages[$page] : collect();
        //$items = collect();
        return new GroupLengthAwarePaginator($items, $total, $pages->keys(), $firstItem, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);

      });
    }
}
