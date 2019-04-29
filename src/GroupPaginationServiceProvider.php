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
      GroupPaginator::viewFactoryResolver(function () {
          return $this->app['view'];
      });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

      $this->loadViewsFrom(__DIR__.'/resources/views', 'group-pagination');

      if ($this->app->runningInConsole()) {
          $this->publishes([
              __DIR__.'/resources/views' => $this->app->resourcePath('views/vendor/group-pagination'),
          ], 'laravel-group-pagination');
      }

      Builder::macro('groupPaginate', function ($column, $start, $length, $order = 'asc', $format = 'Y-m-d', $pageName = 'page') {

        $query = $this->orderBy($column, $order)->get(['*']);

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
            'path' => GroupPaginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);

      });
    }
}
