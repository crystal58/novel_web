<?php
namespace YC;
class Page{
    private $_pageSize;
    private $_curPage;
    private $_totalPage;
    private $_url;
    private $_showPage = 10;
    private $_records;

    public function __construct($count,$curPage,$pageSize){
        $this->_pageSize = $pageSize;
        $this->_curPage = $curPage;
        $this->_records = $count;
        $this->_totalPage  = ceil($count/$pageSize);
    }
    public function getPageHtml(){
        $this->getUrl();
        return $this->getHtml();

    }

    private function getUrl(){
        $url = isset($_SERVER['REQUEST_URI']) ? html_entity_decode($_SERVER['REQUEST_URI']) : '';

        $urlInfo = parse_url($url);
        $path = isset($urlInfo['path']) ? $urlInfo['path'] : '';
        $query = isset($urlInfo['query']) ? $urlInfo['query'] : '';

        $params = array();
        parse_str($query, $params);
        //$params['page'] = $this->_curPage;
        unset($params['page']);

        $this->_url = $path."?".http_build_query($params);
    }

    private function getHtml(){
        $pageHtml = "<div class=\"pagelist\"><span>共".$this->_records."条，共".$this->_totalPage."页</span>";
        $pageHtml .= "<a href='".$this->_url."&page=1'>首页</a>";
        if($this->_curPage > 1){
            $pageHtml .= "<a href='".$this->_url."&page=".($this->_curPage-1)."'>上一页</a>";
        }
        $num = $this->_totalPage > $this->_showPage ? $this->_showPage :$this->_totalPage;
        for($i = 1;$i<= $num ; $i++){
            if($i == $this->_curPage){
                $pageHtml .= "<span class=\"current\">".$this->_curPage."</span>";
                continue;
            }

            if($this->_totalPage <= $num){
                $pageHtml .= "<a href='".$this->_url."&page=".$i."'>".$i."</a>";
                continue;
            }

            if($this->_curPage <= 5){
                $pageHtml .= "<a href='".$this->_url."&page=".$i."'>".$i."</a>";
                continue;
            }else{
                if($i < 6){
                    $p = $this->_curPage - (6-$i);
                    $pageHtml .= "<a href='".$this->_url."&page=".$p."'>".$p."</a>";
                }else if($i > 6){
                    $p = $this->_curPage + ($i-6);
                    $pageHtml .= "<a href='".$this->_url."&page=".$p."'>".$p."</a>";
                }else{
                    $pageHtml .= "<span class=\"current\">".$this->_curPage."</span>";
                }
            }
        }
        if($this->_curPage != $this->_totalPage){
            $pageHtml .= "<a href='".$this->_url."&page=".($this->_curPage+1)."'>下一页</a><a href='".$this->_url."&page=".$this->_totalPage."'>尾页</a>";
        }

        $pageHtml .= "</div>";
        return $pageHtml;

    }

}
