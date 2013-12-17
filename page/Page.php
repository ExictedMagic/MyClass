<?php
/**
 * User: pmsun
 * Date: 13-12-16
 * Time: 下午11:33
 * Desc: 分页类
 * Usage:
 *  $page_template = array(
 *  'blur'  => '<a href="%url%">%page%</a>',//非当前页样式
 *  'focus' => '<span>%page%</span>',//当前页样式
 *  'prev'  => '<a href="%url%">上一页</a>',//上一页样式
 *  'next'  => '<a href="%url%">下一页</a>',//下一页样式
 *  );
 *  $page = new Page(3, $page_template, array("a" => "1", "b" => 2));
 *  echo $page->create($this->_page);
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
    //分页的html模版
    protected $template;

    public function __construct($total, $template = array(), $url_array = array(), $page_size = 5, $per_page = 1) {
        $this->page = 1;
        $this->total = $total;
        $this->page_size = $page_size;
        $this->per_page = $per_page;
        $this->total_page = intval(ceil($total / $per_page));
        $this->url_array = $url_array;
        $this->template = $template;
    }

    public function create($page) {
        $this->page = $page;
        $page_half = intval($this->page_size / 2);
        $page_start = $this->page <= $this->total_page ? max(1, $this->page - $page_half) : 1;
        $page_end =min($page_start + $this->page_size - 1, $this->total_page);
        return $this->pageHtml($page_start, $page_end);
    }

    protected function pageHtml($page_start, $paga_end) {
        $page_array = range($page_start, $paga_end);
        $page_html = "";
        $url_str = empty($this->url_array) ? "?page=" : "?" . http_build_query($this->url_array) . "&page=";
        if (!empty($page_array)) {
            $this->page = $this->total_page >= $this->page ? $this->page : 1;
            //上一页
            $prev_url = $url_str . ($this->page - 1);
            $page_html .= $this->page > 1 ? str_replace("%url%", $prev_url, $this->template['prev']) : "";
            foreach ($page_array as $p) {
                if ($this->page == $p) {
                    $page_html .= str_replace("%page%", $p, $this->template['focus']);
                } else {
                    $url = $url_str . $p;
                    $page_html .= str_replace(array("%url%", "%page%"), array($url, $p), $this->template['blur']);
                }
            }
            //下一页
            $next_url = $url_str . ($this->page + 1);
            $page_html .= $this->page < $this->total_page ? str_replace("%url%", $next_url, $this->template['next']) : "";
        }
        return $page_html;
    }
}