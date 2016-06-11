<?php
?>
<style type="text/css">


    #codeigniter-button-profiler {
        position: fixed;
        z-index:99999;
        bottom:0;
        right: 0;
        height: 40px;
        line-height: 28px;
        /*color: #eee;*/
        color: #181818;
        /*background-color: #181818;*/
        background-color: #e7e7e7;
        padding: 6px 20px 6px 10px;
        font-family: "Helvetica Neue", "Helvetica", Arial, sans-serif;
        font-size: 12px;
        font-weight: normal;
        text-transform: uppercase;
        letter-spacing: 2px;
        border-radius: 5px 0 0;
        border-style: solid;
        border-width: 1px 0 0 1px;
        /*border-color: #111;*/
        border-color: #d1d1d1;
        cursor: pointer;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        -webkit-transition: all ease 400ms;
        -moz-transition: all ease 400ms;
        -ms-transition: all ease 400ms;
        -o-transition: all ease 400ms;
        transition: all ease 400ms;
        text-shadow: 1px 0 0 #fff;
    }
    #codeigniter-button-profiler img {
        height: 28px;
        width: 28px;
        vertical-align: middle;
        text-align: left;
        margin-top:-2px;
    }
    #codeigniter-button-profiler:hover {
        /*background-color: #111111;*/
        /*color:#fff;*/
        background-color: #ebebeb;
        color: inherit;
    }
    #codeigniter-button-profiler.off {
        bottom: -200px;
    }
    #codeigniter-container-profiler {
        position: fixed;
        right: 5%;
        bottom: 5%;
        left: 5%;
        top: 5%;
        -webkit-transition: all ease 600ms;
        -moz-transition: all ease 600ms;
        -ms-transition: all ease 600ms;
        -o-transition: all ease 600ms;
        transition: all ease 600ms;
        visibility: visible;
        opacity:1;
        z-index: 99999;
    }
    #codeigniter-container-profiler.off {
        z-index: -1;
        opacity: 0;
        visibility: hidden;
        top: 120%;
    }
    .codeigniter-full-overlay {
        position: fixed;
        z-index: 99;
        top:0;
        left:0;
        right: 0;
        bottom: 0;
        background-color: rgba(0,0,0,.4);
    }
    .codeigniter-container-inline {
        border-radius: 4px;
        position: absolute;
        z-index: 101;
        height:100%;
        width:100%;
        overflow: auto;
        background-color: #fff;
        padding: 0;
        -webkit-transition: all ease 700ms;
        -moz-transition: all ease 700ms;
        -ms-transition: all ease 700ms;
        -o-transition: all ease 700ms;
        transition: all ease 700ms;
        opacity:1;
        /*         border: 1px solid #c1c1c1; */
    }
    .off .codeigniter-container-inline {
        margin-top: 130%;
        opacity:0;
        overflow: hidden;
        -webkit-transition: all ease 500ms;
        -moz-transition: all ease 500ms;
        -ms-transition: all ease 500ms;
        -o-transition: all ease 500ms;
        transition: all ease 500ms;
    }
    #codeigniter-head-inline {
        position: relative;
        z-index: 30;
        padding: 10px 20px;
        font-size: 17px;
        letter-spacing: 2px;
        /*         background-color: #ebebeb; */
        background-color: #1c2126;
        /*         color: #444; */
        color: #fff;
        text-shadow: 1px 0 0 #000;
        border-bottom: 1px solid #131312;
        font-family: "Helvetica Neue", "Helvetica", Arial, sans-serif;
    }
    #codeigniter-contain-container {
        padding: 10px 25px 10px 15px;
        position: relative;
    }
    #codeigniter_container_sidebar_content > div {
        display: none;
    }
    #codeigniter_container_sidebar_content > div.active_sidebar {
        display: block;
    }
    #codeigniter_sidebar ul {
        list-style: none;
        margin:0;
        padding: 0;
    }
    #codeigniter_sidebar {
        overflow: hidden;
        position: absolute;
        margin-top: -10px;
        margin-left: -15px;
        padding-top: 10px;
        min-height: 100%;
        width: 220px;
        color: #fff;
        background: #39424d;
    }
    #codeigniter_sidebar ul{
        position: relative;
        z-index: 20;
    }
    #codeigniter_sidebar li a {
        text-decoration: none;
        font-size: 14px;
        letter-spacing: 1px;
        font-weight: 200;
        display: block;
        padding: 7px 10px;
        margin: 0;
        color: #fff;
        outline: none;
    }
    #codeigniter_sidebar li.active_sidebar a {
        background-color: #1c2126;
    }
    #codeigniter_sidebar_bg_lay {
        position: absolute;
        z-index: 0;
        width: 220px;
        height: 100%;
        background: #39424d;
        margin-top: 0;
        margin-left: 0;
        padding-top: 10px;
    }
    #codeigniter_container_sidebar_content {
        margin-left: 220px;
    }
</style>
<script type="text/javascript">
    function codeIgniterShow(selector)
    {
        var CIContainer__ = document.getElementById('codeigniter-container-profiler');
        if (CIContainer__) {
            selector.classList.add('off');
            CIContainer__.classList.remove('off');
        }
    }
    function codeIgniterHide()
    {
        var CIButton__ = document.getElementById('codeigniter-button-profiler');
        var CIContainer__ = document.getElementById('codeigniter-container-profiler');
        if (CIButton__ && CIContainer__) {
            CIButton__.classList.remove('off');
            CIContainer__.classList.add('off');
        }
    }
    function code_igniter_open_content(id) {
        var target_parent = document.getElementById('codeigniter_container_sidebar_content');
        var target = document.getElementById(id);
        var target_href = document.getElementById(id + '_href');
        var sidebar_t = document.getElementById('codeigniter_sidebar_list');
        if (target) {
            var chi_prt = sidebar_t.children;
            for (var es =0; chi_prt.length > es; es++) {
                chi_prt[es].classList.remove('active_sidebar');
            }
            var child = target_parent.children;
            for (var i =0; child.length > i; i++) {
                if (typeof child[i] == 'object' && child[i] == target) {
                    child[i].classList.add('active_sidebar');
                    target_href.parentNode.classList.add('active_sidebar');
                } else {
                    child[i].classList.remove('active_sidebar');
                }
            }
        }

    }
</script>
<div id="codeigniter-button-profiler" onclick="codeIgniterShow(this)">
    <img src="data:image/png;base64,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" width="64" height="64" align="left" style="vertical-align: middle">&nbsp;
    Code Igniter 3
</div>
<div id="codeigniter-container-profiler" class="off">
  <div class="codeigniter-full-overlay" onclick="codeIgniterHide()"></div>
  <div class="codeigniter-container-inline">
    <div id="codeigniter-head-inline">Code Igniter Profiler</div>
      <div id="codeigniter_sidebar_bg_lay"></div>
      <div id="codeigniter-contain-container">
<?php
          if (!isset($section)) {
              $section = array();
          }
?>
       <div id="codeigniter_sidebar">
        <ul id="codeigniter_sidebar_list">
<?php
    $c = 0;
    foreach (array_keys($section) as $v) {
        $id = "codeigniter_container_of_".preg_replace('/[^a-z0-9_\-]/i', '-', $v);
        $name = str_replace(array('_', '-'), ' ', $v);
        $name= ucwords($name);
        $active = ' class="active_sidebar"';
        if ($c > 0) {
            $active = '';
        }
        $c++;
?>
          <li<?php echo $active;?>><a href="javascript:void(0);" id="<?php echo $id.'_href';?>" onclick="code_igniter_open_content('<?php echo $id;?>');"><?php echo $name;?></a></li>
<?php
    }
?>
        </ul>
       </div>
       <div id="codeigniter_container_sidebar_content">
<?php
$c =0;
foreach ($section as $k => $v) {
    $id = "codeigniter_container_of_".preg_replace('/[^a-z0-9_\-]/i', '-', $k);
    $active = ' class="active_sidebar"';
    $name = str_replace(array('_', '-'), ' ', $k);
    $name= ucwords($name);
    if ($c > 0) {
        $active = '';
    }
    $c++;
?>
        <div<?php echo $active;?> id="<?php echo $id;?>">
        <table style="width: 100%;margin: 1em;">
            <?php
                if (is_array($v)) {
                    foreach ($v as $key => $item) {
                        echo "<tr>";
                        if ($k =='benchmarks') {
                            $item = $item . ' '. __('seconds');
                        }

                        $item = print_r($item, true);
                        echo "<td style='padding: 5px 10px;background: #e4e6e7;border-bottom: 3px solid #39424d;'>{$key}</td>
<td style='background: #f6f6f6;padding: 5px 10px;border-bottom: 3px solid #999;'>{$item}</td>";
                        echo "</tr>";
                    }
                } else {
                    if (empty($v)) {
                        $v = sprintf(__('No data for: %s'), $name);
                    } else {
                        if ($k =='memory_usage') {
                            $v = $v . ' '. __('bytes');
                        }
                        $v = print_r($v, true);
                    }
                    echo "<td style='padding: 5px 10px;background: #e4e6e7;border-bottom: 3px solid #39424d;'>{$v}</td>";
                }
            ?>
        </table>
        </div>
<?php
}
?>
       </div>
     </div>
  </div>
</div>
