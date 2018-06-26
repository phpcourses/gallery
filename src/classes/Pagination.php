<?php

class Pagination extends DataEntity
{
    const IMAGE_COUNT = 9;
    /** @var int count of pages */
    private $pageCount = false;

    /** Return qty of pages
     *
     * @return float|int
     */
    private function getPageCount()
    {
        if (!$this->pageCount) {
            $result = $this->request('SELECT COUNT(id) FROM images');
            return ceil($result->fetchColumn(0) / self::IMAGE_COUNT);
        } else {
            return $this->pageCount;
        }
    }

    /** Get last page number
     *
     * @return int
     */
    private function getLastPage(): int
    {
        return $this->getPageCount();
    }

    /** Get first page, first page is 1
     *
     * @return int
     */
    private function getFirstPage()
    {
        return 1;
    }

    /** Get next page number
     *
     * @return bool|int
     */
    private function getNextPage()
    {
        if (isset($_REQUEST['p']) && $this->getPageCount() <= $_REQUEST['p']) {
            return false;
        } elseif (isset($_REQUEST['p'])) {
            return $_REQUEST['p'] + 1;
        } else {
            return 2;
        }
    }

    /** Get previous page number
     *
     * @return bool|int
     */
    private function getPrevPage()
    {
        return isset($_REQUEST['p']) && $_REQUEST['p'] > 1 ? $_REQUEST['p'] - 1 : false;
    }

    /** Get current page number
     *
     * @return int
     */
    private function getCurrentPage()
    {
        return isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
    }

    /** Generate pagination HTML
     *
     * @return string
     */
    public function render()
    {
        $html = '';
        if ($this->getPageCount() > 1) {
            $html .= "<li class='page-item'><a class='page-link' href='/?p=" . $this->getFirstPage() . "'>Go to first page</a></li>";
            if ($prevPage = $this->getPrevPage()) {
                $html .= "<li class='page-item'><a class='page-link' href='/?p=" . $prevPage . "'>" . $prevPage . "</a></li>";
            }
            $html .= "<li class='page-item active'><a class='page-link' href='#'>" . $this->getCurrentPage() . "</a></li>";
            if ($nextPage = $this->getNextPage()) {
                $html .= "<li class='page-item'><a class='page-link' href='/?p=" . $nextPage . "'>" . $nextPage . "</a></li>";
            }
            $html .= "<li class='page-item'><a class='page-link' href='/?p=" . $this->getLastPage() . "'>Go to last page</a></li>";
        }

        return $html;
    }
}
