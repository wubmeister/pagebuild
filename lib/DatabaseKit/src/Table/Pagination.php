<?php

namespace DatabaseKit\Table;

class Pagination
{
    public function __construct($itemCount, $page, $limit)
    {
        $pageCount = max(1, ceil($itemCount / $itemsPerPage));
        $currentPageNumber = min($currentPageNumber, $pageCount);
        $offset = ($currentPageNumber - 1) * $itemsPerPage;

        $this->pageCount        = $pageCount;
        $this->itemCountPerPage = $itemsPerPage;
        $this->first            = 1;
        $this->current          = $currentPageNumber;
        $this->last             = $pageCount;

        // Previous and next
        if ($currentPageNumber - 1 > 0) {
            $this->previous = $currentPageNumber - 1;
        }

        if ($currentPageNumber + 1 <= $pageCount) {
            $this->next = $currentPageNumber + 1;
        }

        // Pages in range
        // $scrollingStyle = $this->_loadScrollingStyle($scrollingStyle);
        // $this->pagesInRange     = $scrollingStyle->getPages($this);
        // $this->firstPageInRange = min($this->pagesInRange);
        // $this->lastPageInRange  = max($this->pagesInRange);

        $this->pagesInRange = [];
        for ($i = 1; $i <= $pageCount; $i++) {
            $this->pagesInRange[] = $i;
        }
        $this->firstPageInRange = $this->first;
        $this->lastPageInRange  = $this->last;

        // Item numbers
        $this->currentItemCount = $currentPageNumber == $this->last ? $itemCount - $offset : $itemsPerPage;
        $this->totalItemCount   = $itemCount;
        $this->firstItemNumber  = $offset;
        $this->lastItemNumber   = $offset + $this->currentItemCount;
    }
}
