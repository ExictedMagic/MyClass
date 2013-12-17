<?php
/**
 * User: pmsun
 * Date: 13-12-16
 * Time: 下午11:33
 * Desc: 分页类
 * Usage: $page = new Page(100, array('a' => 1, 'b' => '2')); $page->create($current_page);
 */
class Page {
    //当前页码
    protected $page;
    //总数
    protected $total;
    //总页数
    protected $total_page;
    //每页显示数量
    protected $per_page;
    //页码显示的数量
    protected $page_size;
    //用于构造地址的参数，例如一些查询条件
    protected $url_array;

    public function __construct($total, $url_array = array(), $page_size = 5, $per_page = 10) {
        $this->page = 1;
        $this->total = $total;
        $this->page_size = $page_size;
        $this->per_page = $per_page;
        $this->total_page = intval(ceil($total / $per_page));
        $this->url_array = $url_array;
    }

    public function create($page) {
        $this->page = $page;
        $page_half = intval($this->page_size / 2);
        $page_start = max(1, $this->page - $page_half);
        $page_end = min($page_start + $this->page_size - 1, $this->total_page);
        return $this->pageHtml($page_start, $page_end);
    }

    protected function pageHtml($page_start, $paga_end) {
        $page_array = range($page_start, $paga_end);
        $page_html = "";
        $url_str = empty($this->url_array) ? "?page=" : "?" . http_build_query($this->url_array) . "&page=";
        if (!empty($page_array)) {
            foreach ($page_array as $p) {
                if ($this->page == $p) {
                    $page_html .= "<span style='color:red;'>{$p}</span>";
                } else {
                    $page_html .= "<a href='{$url_str}{$p}'>{$p}</a>";
                }
            }
        }
        return $page_html;
    }
}
