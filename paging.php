<?php
class Paging
{
    protected $base_url         = "";           // 기본 연결링크
    protected $page_rows        = 0;    // 한페이지에 출력할 Row 수
    protected $total_rows       = 0;    // 총 Row수
    protected $display_always   = TRUE; // 페이지가 1페이지만 있어도 출력할건지 여부 
    protected $fixed_page_num       = 10;    // 한번에 표시할 페이지 수
    
    protected $first_link = '<<';     // [처음] 버튼에 표시할 문자
    protected $next_link = '>';               // [다음] 버튼에 표시할 문자
    protected $prev_link = '<';                // [이전] 버튼에 표시할 문자
    protected $last_link = '>>';    // [마지막] 버튼에 표시할 문자
    
    protected $full_tag_open = '<ul class="pagination">';   // 전체를 감싸는 여는 태그
    protected $full_tag_close = '</ul>';                    // 전체를 감싸는 닫는 태그
    protected $item_tag_open = "<li class='page-item'>";                      // 각 페이지 링크 여는 태그
    protected $item_tag_close = "</li>";                    // 각 페이지 링크 닫는 태그
    protected $cur_tag_open = "<li class='page-item'>";        // 현재 페이지 링크 여는 태그
    protected $cur_tag_close = "</li>";                     // 현재 페이지 링크 닫는 태그
    
    protected $first_tag_open = "<li class='page-item'>"; 
    protected $first_tag_close = '</li>';
    protected $last_tag_open = "<li class='page-item'>"; 
    protected $last_tag_close = '</li>';
    protected $next_tag_open = "<li class='page-item'>"; 
    protected $next_tag_close = '</li>';
    protected $prev_tag_open = "<li class='page-item'>"; 
    protected $prev_tag_close = '</li>';
    
    protected $page_param   = "page";       // 패러미터 이름

    protected $display_pages = TRUE;
    
    protected $disable_first_link   = TRUE; // 현재 페이지가 첫페이지일때 [처음] 링크를 disabled 시킨다..
    protected $disable_last_link    = TRUE; // 현재 페이지가 마지막페이지일때 [마지막] 링크를 disabled 시킨다..
    protected $disable_prev_link    = TRUE; // 현재 페이지가 처음페이지일때 [이전] 링크를 disabled 시킨다..
    protected $disable_next_link    = TRUE; // 현재 페이지가 마지막페이지일때 [마지막] 링크를 disabled 시킨다.
    
    protected $display_first_always = TRUE; // 현재페이지가 첫페이일때 [처음] 링크를 보여준다..
    protected $display_last_always  = TRUE; // 현재페이지가 마지막페이지일때 [마지막] 링크를 보여준다..
    protected $display_prev_always  = TRUE; // 현재페이지가 첫번째페이지일때 [이전] 링크를 보여준다..
    protected $display_next_always  = TRUE; // 현재페이지가 마지막페이지일때 [다음] 링크를 보여준다.
    
    protected $disabled_first_tag_open  = '<li class="disabled">';
    protected $disabled_first_tag_close = '</li>';
    protected $disabled_last_tag_open   = '<li class="disabled">';
    protected $disabled_last_tag_close  = '</a></li>';
    protected $disabled_prev_tag_open   = '<li class="disabled">';
    protected $disabled_prev_tag_close  = '</li>';
    protected $disabled_next_tag_open   = '<li class="disabled">';
    protected $disabled_next_tag_close  = '</li>';
    
    // 내부 사용변수
    protected $page             = 1;    // 현재 페이지
    protected $add_param = ''; 
    
    function __construct($params = array())
    {
        $this->initialize($params);
    }   
    
    // 넘겨받은 설정값으로 세팅값을 변경한다.
    public function initialize(array $params = array())
    {
        foreach ($params as $key => $val)
        {
            if (property_exists($this, $key))
            {
                $this->$key = $val;
            }
        }
        return $this;
    }
    
    public function create()
    {
        
        // 만약 총 Rows가 0이거나, 한줄당 표시가 0 인경우는 return 한다.
        if ($this->total_rows == 0 OR $this->page_rows == 0) return '';
        
        // 총 몇페이지가 나올지 계산한다
        $num_pages = (int) ceil($this->total_rows / $this->page_rows);

        // $display_always 값이 FALSE 이고 페이지가 하나일경우 return 한다.
        if ($this->display_always === FALSE AND $num_pages === 1) return '';

        // 한번에 표시할 페이지수를 체크한다.
        // 값이 잘못되어있다면 아무것도 표시하지 않는다.
        $this->fixed_page_num = (int) $this->fixed_page_num;
        if ($this->fixed_page_num < 0) return "";       

        // 앞부분 링크 URL을 만든다. 
        $base_url = trim($this->base_url);      

        // 주소 URL에서 현재 page를 가져온다.
        $this->page =  (isset($_GET[$this->page_param]) && $_GET[$this->page_param])?$_GET[$this->page_param]:1;
        
        // 추가 파라미터 가져온다.
        if (isset($this->page)) {
            $arr_param = is_array($_GET) ? $_GET : array();
            unset($arr_param[$this->page_param]);

            $this->add_param = http_build_query($arr_param);
        }

        
        $this->cur_page = (int) $this->page;        
        
        // 페이지 값이 총 페이지수를 넘지 않는지 확인한다.
        // 만약 더 많다면, 총 페이지값으로 치환해준다.
        if ($this->cur_page > $num_pages)
        {
            $this->cur_page = $num_pages;
        }
                
        $uri_page_number = $this->cur_page;

        // 시작과 종료 페이지 번호를 얻어온다.     
        $start = (ceil($this->cur_page / $this->fixed_page_num) - 1) * $this->fixed_page_num + 1 + 1; // Last plus one is for loop statement starts $start - 1 
        $end   = (ceil($this->cur_page / $this->fixed_page_num) == ceil($num_pages / $this->fixed_page_num)) ? $num_pages : ceil($this->cur_page / $this->fixed_page_num) * $this->fixed_page_num;
        

        // Return 할 문자열을 만든다.
        $output = '';

        // [처음으로] 버튼 만들기
        if ($this->first_link !== FALSE)
        {           
            if ($this->display_first_always === TRUE AND $this->disable_first_link === TRUE AND $this->cur_page == 1)
            {
                $output .= $this->disabled_first_tag_open . "<a class='page-link' href='#'>" . $this->first_link . "</a>" . $this->disabled_first_tag_close;
            }
            else if ($this->display_first_always === TRUE OR $this->cur_page != 1)
            {
                $output .= $this->first_tag_open.'<a class="page-link" href="'.$base_url.'?'.$this->page_param.'=1&'.$this->add_param.'">'.$this->first_link.'</a>'.$this->first_tag_close;
            }
        }

        // [이전] 버튼 만들기
        if ($this->prev_link !== FALSE)
        {
            if ($this->display_prev_always === TRUE AND $this->disable_prev_link === TRUE AND $this->cur_page == 1)
            {
                $output .= $this->disabled_prev_tag_open . "<a class='page-link' href='#'>" . $this->prev_link . "</a>" . $this->disabled_prev_tag_close;
            }
            else if ($this->display_prev_always === TRUE OR $this->cur_page != 1)
            {
                // 이전페이지 번호를 가져온다. 단, 현재페이지가 1이면, 이전페이지도 1을 가져온다.
                $i = ($uri_page_number == 1) ? 1 : ( $uri_page_number - 1);
                
                $output .= $this->prev_tag_open.'<a class="page-link" href="'.$base_url.'?'.$this->page_param."=".$i.'&'.$this->add_param.'">'
                        .$this->prev_link.'</a>'.$this->prev_tag_close;
            }
        }

        // 각 페이지 버튼을 만든다.
        if ($this->display_pages !== FALSE)
        {           
            for ($loop = $start -1; $loop <= $end; $loop++)
            {
                $i = $loop;             

                if ($i >= 1)
                {
                    if ($this->cur_page == $loop)
                    {
                        // 현재 페이지일 경우
                        $output .= $this->cur_tag_open.'<a class="page-link" href="#"><b>'.$loop.'</b></a>'.$this->cur_tag_close;
                    }
                    else
                    {                       
                        $output .= $this->item_tag_open.'<a class="page-link" href="'.$base_url.'?'.$this->page_param."=".$loop.'&'.$this->add_param.'">'.$loop.'</a>'.$this->item_tag_close;
                    }
                }
            }
        }

        // 다음으로 버튼을 만든다.
        if ($this->next_link !== FALSE)
        {
            if ($this->display_next_always === TRUE AND $this->disable_next_link === TRUE AND $this->cur_page == $num_pages)
            {
                $output .= $this->disabled_next_tag_open . "<a class='page-link' href='#'>" . $this->next_link . "</a>" . $this->disabled_next_tag_close;
            }
            else if ($this->display_next_always === TRUE OR $this->cur_page != $num_pages)
            {
                // 다음페이지를 계산해준다. 현재페이지가 마지막페이지라면, 현재페이지를 세팅
                $i = ($this->cur_page == $num_pages)? $num_pages : $this->cur_page + 1;
                
                $output .= $this->next_tag_open.'<a class="page-link" href="'.$base_url.'?'.$this->page_param."=".$i.'&'.$this->add_param.'">'.$this->next_link.'</a>'.$this->next_tag_close;
            }
        }

        // [마지막으로 페이지를 만든다.]
        if ($this->last_link !== FALSE)
        {
            if ($this->display_last_always === TRUE AND $this->disable_last_link === TRUE AND $this->cur_page == $num_pages)
            {
                $output .= $this->disabled_last_tag_open . "<a class='page-link' href='#'>" . $this->last_link . "</a>" . $this->disabled_last_tag_close;
            }
            else if ($this->display_last_always === TRUE OR $this->cur_page != $num_pages)
            {
                $i = $num_pages;

                $output .= $this->last_tag_open.'<a class="page-link" href="'.$base_url.'?'.$this->page_param."=".$i.'&'.$this->add_param.'">'.$this->last_link.'</a>'.$this->last_tag_close;
            }
        }

        // 완성된 return 값을 정리한다.
        $output = preg_replace('#([^:])//+#', '\\1/', $output);
        
        return $this->full_tag_open.$output.$this->full_tag_close;
    }
    
    
    function __destruct()
    {       
    }
}


