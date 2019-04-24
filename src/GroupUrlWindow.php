<?php

namespace GrofGraf\LaravelGroupPagination;

class GroupUrlWindow extends \Illuminate\Pagination\UrlWindow
{

    protected function getSmallSlider()
    {
        return [
            'first'  => $this->paginator->getPageRange($this->firstPage(), $this->lastPage()),
            'slider' => null,
            'last'   => null,
        ];
    }

    /**
     * Create a URL slider links.
     *
     * @param  int  $onEachSide
     * @return array
     */
    protected function getUrlSlider($onEachSide)
    {
        $window = $onEachSide * 2;
        if (! $this->hasPages()) {
            return ['first' => null, 'slider' => null, 'last' => null];
        }

        // If the current page is very close to the beginning of the page range, we will
        // just render the beginning of the page range, followed by the last 2 of the
        // links in this list, since we will not have room to create a full slider.
        if ($this->currentPage() <= $this->pages()[$window]) {
            return $this->getSliderTooCloseToBeginning($window);
        }

        // If the current page is close to the ending of the page range we will just get
        // this first couple pages, followed by a larger window of these ending pages
        // since we're too close to the end of the list to create a full on slider.
        elseif ($this->currentPage() > ($this->pages()[count($this->pages()) - $window + 1])) {
            return $this->getSliderTooCloseToEnding($window);
        }

        // If we have enough room on both sides of the current page to build a slider we
        // will surround it with both the beginning and ending caps, with this window
        // of pages in the middle providing a Google style sliding paginator setup.
        return $this->getFullSlider($onEachSide);
    }

    /**
     * Get the slider of URLs when too close to beginning of window.
     *
     * @param  int  $window
     * @return array
     */
    protected function getSliderTooCloseToBeginning($window)
    {
        return [
            'first' => $this->paginator->getPageRange($this->firstPage(), $this->pages()[$window + 2]),
            'slider' => null,
            'last' => $this->getFinish(),
        ];
    }

    protected function getSliderTooCloseToEnding($window)
    {
        $last = $this->paginator->getPageRange(
            $this->pages()[$this->pages()->count() - ($window + 3)],
            $this->lastPage()
        );

        return [
            'first' => $this->getStart(),
            'slider' => null,
            'last' => $last,
        ];
    }


    public function get()
    {
        $onEachSide = $this->paginator->onEachSide;

        if ($this->pages()->keys()->last() < ($onEachSide * 2) + 6) {
            return $this->getSmallSlider();
        }

        return $this->getUrlSlider($onEachSide);
    }

    /**
     * Determine if the underlying paginator being presented has pages to show.
     *
     * @return bool
     */
    public function hasPages()
    {
        return $this->paginator->lastPage() != $this->paginator->firstPage();
    }

    protected function pages(){
      return $this->paginator->pages();
    }

    protected function firstPage(){
      return $this->paginator->firstPage();
    }

    /**
     * Get the current page from the paginator.
     *
     * @return int
     */
    protected function currentPage()
    {
        return $this->paginator->currentPage();
    }

    protected function pageIndex($page){
      return $this->paginator->pageIndex($page);
    }

    /**
     * Get the last page from the paginator.
     *
     * @return int
     */
    protected function lastPage()
    {
        return $this->paginator->lastPage();
    }

    public function getStart()
    {
        return $this->paginator->getPageRange($this->firstPage(), $this->pages()[1]);
    }

    public function getFinish()
    {
        return $this->paginator->getPageRange(
            $this->pages()[$this->pages()->count() - 2],
            $this->lastPage()
        );
    }

    public function getAdjacentUrlRange($onEachSide)
    {
        return $this->paginator->getPageRange(
            $this->pages()[$this->pageIndex($this->currentPage()) - $onEachSide],
            $this->pages()[$this->pageIndex($this->currentPage()) + $onEachSide]
        );
    }
}
