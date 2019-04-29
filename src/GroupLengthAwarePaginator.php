<?php

namespace GrofGraf\LaravelGroupPagination;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;

class GroupLengthAwarePaginator extends \Illuminate\Pagination\LengthAwarePaginator
{

  protected $onPage;
  protected $firstPage;
  protected $pages;
  protected $firstItem;

  public static $defaultView = 'group-pagination::bootstrap-4';

  public function __construct($items, $total, $pages, $firstItem, $currentPage = null, array $options = [])
  {
      $this->options = $options;

      foreach ($options as $key => $value) {
          $this->{$key} = $value;
      }

      $this->total = $total;
      $this->firstItem = $firstItem;
      $this->onPage = $items->count();
      $this->lastPage = $pages->count();
      $this->pages = $pages;
      $this->path = $this->path !== '/' ? rtrim($this->path, '/') : $this->path;
      $this->currentPage = $this->setCurrentPage($currentPage, $this->pageName);
      $this->items = $items instanceof Collection ? $items : Collection::make($items);
  }

  protected function onPage(){
    return $this->onPage;
  }

  public function pageIndex($page){
    return $this->pages->search($page);
  }

  public function pages(){
    return $this->pages;
  }

  public function getPageRange($start, $end)
  {
    $pages = $this->pages;
    $start = $pages->search($start);
    $end = $pages->search($end);

    return($pages->slice($start, ($end + 1) - $start)->mapWithKeys(function ($page) use ($pages){
        return [$page => $this->url($page)];
    })->all());
  }

  protected function elements()
  {
      $window = GroupUrlWindow::make($this);

      return array_filter([
          $window['first'],
          is_array($window['slider']) ? '...' : null,
          $window['slider'],
          is_array($window['last']) ? '...' : null,
          $window['last'],
      ]);
  }

  protected function setCurrentPage($currentPage, $pageName)
  {
    $pages = $this->pages;
    $currentPage = $currentPage ?: static::resolveCurrentPage($pageName, 1, $pages);
    if($this->isValidPageNumber($currentPage)){
      return $currentPage ? $currentPage : (count($this->pages) ? $this->pages->first() : 1);
    }
    return count($this->pages) ? $this->pages->first() : 1;
  }

  public function previousPageUrl()
  {
    $pages = $this->pages();
    $pageIndex = $this->pageIndex($this->currentPage());
    if (count($pages) && $pageIndex) {
      return $this->url($pages[$pageIndex - 1]);
    }
  }

  public static function resolveCurrentPage($pageName = 'page', $default = 1, $pages = [])
  {
      if (isset(static::$currentPageResolver)) {
        return call_user_func(static::$currentPageResolver, $pageName, $default, $pages);
      }

      return $default;
  }

  public function nextPageUrl()
  {
    $pages = $this->pages;
    if ($this->hasMorePages()) {
      return $this->url($pages[$pages->search($this->currentPage()) + 1]);
    }
  }

  public function hasMorePages()
  {
    $pages = $this->pages;
    return count($pages) && $pages->search($this->currentPage()) !== false && $pages->search($this->currentPage())  < $pages->search($pages->last());
  }

  public function hasPages()
  {
      return $this->pages->search($this->currentPage()) !== 0 || $this->hasMorePages();
  }

  public function lastPage(){
    return $this->pages->last();
  }

  public function firstPage(){
    return $this->pages->first();
  }

  protected function isValidPageNumber($page)
  {
    $pageExists = $this->pages->search(function($i) use ($page){
      return $i == $page;
    });
    return $pageExists !== false;
  }

  public function firstItem()
  {
     return count($this->items) > 0 ? $this->firstItem : null;
  }

  public function onFirstPage()
  {
      return $this->currentPage() == $this->pages->first();
  }

  public function toArray()
  {
      return [
          'current_page' => $this->currentPage(),
          'data' => $this->items->toArray(),
          'first_page_url' => $this->url($this->firstPage()),
          'from' => $this->firstItem(),
          'last_page' => $this->lastPage(),
          'last_page_url' => $this->url($this->lastPage()),
          'next_page_url' => $this->nextPageUrl(),
          'path' => $this->path,
          'on_page' => $this->onPage(),
          'prev_page_url' => $this->previousPageUrl(),
          'to' => $this->lastItem(),
          'total' => $this->total(),
      ];
  }

  public function links($view = null, $data = [])
  {
      return $this->render($view, $data);
  }

  public function render($view = null, $data = [])
  {
      return new HtmlString(static::viewFactory()->make($view ?: static::$defaultView, array_merge($data, [
          'paginator' => $this,
          'elements' => $this->elements(),
      ]))->render());
  }

  public static function useBootstrapThree()
  {
    static::defaultView('group-pagination::default');
  }

}
