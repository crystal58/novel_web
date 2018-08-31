<?php
namespace YC;
class Page{
    private $_pageSize;
    private $_curPage;
    private $_totalPage;
    private $_url;
    private $_showPage = 10;
    private $_records;

    public function __construct($count,$curPage,$pageSize,$url=""){
        $this->_pageSize = (int)$pageSize;
        $this->_curPage = (int)$curPage;
        $this->_records = (int)$count;
        $this->_totalPage  = ceil($count/$pageSize);
        $this->_url = $url;
    }
    public function getPageHtml(){
        if(!empty($this->_url)){
            $result = $this->getWebHtml();
        }else{
            $this->getUrl();
            $result = $this->getHtml();
        }
        return $result;

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

    private function getWebHtml(){
        $pageHtml = "<div class=\"pagelist\"><span>共".$this->_records."条，共".$this->_totalPage."页</span>";
        $firstPage = str_replace("{num}",1,$this->_url);
        $pageHtml .= "<a href='".$firstPage."'>首页</a>";
        if($this->_curPage > 1){
            $pageHtml .= "<a href='".str_replace("{num}",($this->_curPage-1),$this->_url)."'>上一页</a>";
        }
        $num = $this->_totalPage > $this->_showPage ? $this->_showPage :$this->_totalPage;
        for($i = 1;$i<= $num ; $i++){

            if($this->_curPage == $i){
                $pageHtml .= "<span class=\"current\">".$i."</span>";
                continue;
            }

            if($this->_totalPage <= $num){
                $pageHtml .= "<a href='".str_replace("{num}",$i,$this->_url)."'>".$i."</a>";
                continue;
            }

            if($this->_curPage <= 5){
                $pageHtml .= "<a href='".str_replace("{num}",$i,$this->_url)."'>".$i."</a>";
                continue;
            }else{
                if($i < 6){
                    $p = $this->_curPage - (6-$i);
                    $pageHtml .= "<a href='".str_replace("{num}",$p,$this->_url)."'>".$p."</a>";
                }else if($i > 6){
                    $p = $this->_curPage + ($i-6);
                    $pageHtml .= "<a href='".str_replace("{num}",$p,$this->_url)."'>".$p."</a>";
                }else{
                    $pageHtml .= "<span class=\"current\">".$this->_curPage."</span>";
                }
            }
        }
        if($this->_curPage != $this->_totalPage){
            $pageHtml .= "<a href='".str_replace("{num}",($this->_curPage+1),$this->_url)."'>下一页</a><a href='".str_replace("{num}",$this->_totalPage,$this->_url)."'>尾页</a>";
        }

        $pageHtml .= "</div>";
        return $pageHtml;

    }


    private function getHtml(){
        $pageHtml = "<div class=\"pagelist\"><span>共".$this->_records."条，共".$this->_totalPage."页</span>";
        $pageHtml .= "<a href='".$this->_url."&page=1'>首页</a>";
        if($this->_curPage > 1){
            $pageHtml .= "<a href='".$this->_url."&page=".($this->_curPage-1)."'>上一页</a>";
        }
        $num = $this->_totalPage > $this->_showPage ? $this->_showPage :$this->_totalPage;
        for($i = 1;$i<= $num ; $i++){

            if($this->_curPage == $i){
                $pageHtml .= "<span class=\"current\">".$i."</span>";
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
